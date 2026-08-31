<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use OpenApi\Attributes as OA;
use ReflectionAttribute;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The API is versioned per endpoint, not as a whole: the documented server url ends at
 * `/api` and every operation carries its own version as the first path segment. These
 * tests keep that convention honest, because a forgotten version prefix produces a
 * documentation entry that silently points at a url nobody serves.
 */
class ApiDocumentationTest extends TestCase
{
    private const OPERATION_ATTRIBUTES = [
        OA\Get::class,
        OA\Post::class,
        OA\Put::class,
        OA\Patch::class,
        OA\Delete::class,
        OA\Head::class,
        OA\Options::class,
    ];

    public function test_every_documented_operation_declares_its_own_api_version(): void
    {
        $operations = $this->documentedOperations();
        $this->assertNotEmpty($operations, 'No documented operations found. Did the controller namespace move?');

        $withoutVersion = array_filter(
            $operations,
            static fn (array $operation) => preg_match('#^/v\d+/#', $operation['path']) !== 1,
        );

        $this->assertSame(
            [],
            array_column($withoutVersion, 'source'),
            'Every documented path must start with its API version, for example "/v1/status/{id}". '
            . 'The version belongs into the path so that a single endpoint can be raised to a new '
            . 'version without moving the rest of the API along with it.',
        );
    }

    public function test_every_documented_operation_is_actually_routed(): void
    {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            if (!str_starts_with($route->uri(), 'api/')) {
                continue;
            }
            foreach ($route->methods() as $method) {
                $routes[] = $method . ' ' . $this->normalize('/' . substr($route->uri(), strlen('api/')));
            }
        }

        $undocumented = [];
        foreach ($this->documentedOperations() as $operation) {
            $needle = $operation['method'] . ' ' . $this->normalize($operation['path']);
            if (!in_array($needle, $routes, true)) {
                $undocumented[] = $operation['source'] . ' documents ' . $needle;
            }
        }

        $this->assertSame(
            [],
            $undocumented,
            'Documented operations without a matching route. Usually the version prefix of the path '
            . 'does not match the route group it is registered in.',
        );
    }

    /**
     * Path parameters are compared by position, not by name: the documentation and the route
     * definition disagree about names in a number of places, which is a separate problem.
     */
    private function normalize(string $path): string
    {
        return preg_replace('/\{[^}]+\}/', '{}', rtrim($path, '/'));
    }

    /**
     * @return list<array{method: string, path: string, source: string}>
     */
    private function documentedOperations(): array
    {
        $operations = [];

        foreach (Finder::create()->files()->name('*.php')->in(app_path('Http/Controllers/API')) as $file) {
            $class = $this->classNameOf($file);
            if (!class_exists($class)) {
                continue;
            }

            foreach (new ReflectionClass($class)->getMethods() as $method) {
                foreach ($method->getAttributes() as $attribute) {
                    if (!in_array($attribute->getName(), self::OPERATION_ATTRIBUTES, true)) {
                        continue;
                    }

                    $operations[] = [
                        'method' => strtoupper(class_basename($attribute->getName())),
                        'path' => $this->pathOf($attribute),
                        'source' => class_basename($class) . '::' . $method->getName(),
                    ];
                }
            }
        }

        return $operations;
    }

    private function pathOf(ReflectionAttribute $attribute): string
    {
        $arguments = $attribute->getArguments();

        // Operations are always written with named arguments in this codebase, but path is
        // also the first positional argument of every OA operation attribute.
        return $arguments['path'] ?? $arguments[0] ?? '';
    }

    private function classNameOf(SplFileInfo $file): string
    {
        $relative = str_replace([app_path() . DIRECTORY_SEPARATOR, '.php'], '', $file->getRealPath());

        return 'App\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
    }
}
