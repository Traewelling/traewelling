<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProfilePictureService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebFingerController extends Controller
{
    private string $resource;

    private string $serverName;

    public function __construct(string $resource)
    {
        $this->resource = $resource;
        $this->serverName = self::getServerName(config('app.url'));
    }

    public static function getServerName(string $url): string
    {
        $parsedUrl = parse_url($url);
        $protocol = $parsedUrl['scheme'];
        $serverName = $parsedUrl['host'];

        if (isset($parsedUrl['port'])) {
            $serverName = WebFingerController::appendNonStandardPort($protocol, $serverName, $parsedUrl['port']);
        }

        return $serverName;
    }

    public function renderResponse(): JsonResponse
    {
        try {
            [$username, $host] = $this->parseDetails($this->resource);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid resource format.');
        }

        if ($host != $this->serverName) {
            throw new InvalidArgumentException('Only users from ' . $this->serverName . ' are accepted.');
        }

        $user = User::where('username', $username)->first();
        if ($user == null) {
            throw new NotFoundHttpException("Couldn't find user");
        }

        $avatarUrl = resolve(ProfilePictureService::class)->getUrl($user);

        return new JsonResponse(
            [
                'subject' => 'acct:' . $username . '@' . $this->serverName,
                'aliases' => [
                    route('profile', ['username' => $username]),
                ],
                'links' => [
                    [
                        'rel' => 'http://webfinger.net/rel/profile-page',
                        'type' => 'text/html',
                        'href' => route('profile', ['username' => $username]),
                    ],
                    [
                        'rel' => 'http://webfinger.net/rel/avatar',
                        'type' => 'image/png',
                        'href' => $avatarUrl,
                    ],
                ],
            ],
            200,
            [
                'Content-Type' => 'application/jrd+json',
            ],
        );
    }

    public function parseDetails(string $resource): mixed
    {
        // is it a 'acct:' uri?
        if (str_starts_with($resource, 'acct:')) {
            $atPos = strpos($resource, '@', strlen('acct:'));
            $username = substr($resource, strlen('acct:'), $atPos - strlen('acct:'));
            $host = substr($resource, $atPos + 1);

            return [
                $username,
                $host,
            ];
        }
        // otherwise it's a regular url
        // e.g. https://traewelling.de/@Gertrud123
        $url = parse_url($resource);
        $username = substr($url['path'], 2);
        $host = $url['host'];
        $scheme = $url['scheme'];
        if (isset($url['port'])) {
            $host = $this->appendNonStandardPort($scheme, $host, $url['port']);
        }

        return [
            $username,
            $host,
        ];
    }

    public static function appendNonStandardPort(string $scheme, string $host, ?int $port): string
    {
        if ($scheme == 'http' && $port != 80) {
            $host .= ':' . $port;
        }

        if ($scheme == 'https' && $port != 443) {
            $host .= ':' . $port;
        }

        return $host;
    }
}
