<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Checkin;
use App\Models\Status;
use App\Models\User;
use App\Models\Webhook;
use App\Models\YearInReviewCache;
use App\Services\PersonalDataSelection\Exporters\Base\AbstractExporter;
use App\Services\PersonalDataSelection\Exporters\StatusExporter;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

/**
 * Audits the GDPR personal-data export for completeness.
 *
 * Run with:  php artisan gdpr:audit
 * Exit code: 0 = no critical issues, 1 = critical issues found
 *
 * When a column or relation is intentionally excluded from the export, add it to
 * INTENTIONALLY_EXCLUDED_COLUMNS or INTENTIONALLY_EXCLUDED_RELATIONS below with a
 * justification. This makes deliberate omissions visible and auditable, and
 * prevents the same question from surfacing in every future audit run.
 */
class AuditGdprExport extends Command
{
    protected $signature = 'gdpr:audit {--strict : Treat warnings as failures (exit code 1)}';

    protected $description = 'Audit the GDPR export for missing relations, phantom columns, and uncovered model fields.';

    private const string DIVIDER = '──────────────────────────────────────────────────────';

    /**
     * Columns intentionally excluded from the GDPR export.
     * Format: 'db_table' => ['column' => 'justification']
     *
     * Every omission should be documented here so audits stay clean and decisions
     * are visible to future contributors.
     */
    private const INTENTIONALLY_EXCLUDED_COLUMNS = [
        'users' => [
            'id' => 'Internal integer PK; uuid is the user-facing identifier and is exported',
            'password' => 'Security credential — must never be exported',
            'remember_token' => 'Session token — must never be exported',
            'recent_gdpr_export' => 'Internal rate-limit timestamp, not personal data',
            'avatar' => 'Avatar image is exported as a file by UserGdprDataService',
        ],
        'social_login_profiles' => [
            'id' => 'internal',
            'mastodon_token' => 'secret',
        ],
        'activity_log' => [
            'attribute_changes' => 'Might have sensitive data of other users, if admin or moderator',
        ],
        'event_suggestions' => [
            'telegram_notification_id' => 'internal notification',
            'matrix_notification_id' => 'internal notification',
        ],
        'oauth_access_tokens' => [
            'id' => 'internal',
        ],
        'oauth_clients' => [
            'secret' => 'Secrets must not be exported',
        ],
        'password_resets' => [
            'token' => 'Is a secret, which must not be exported',
        ],
        'reports' => [
            'id' => 'Internal integer PK; not relevant to the user',
            'status' => 'User will not see the status of a report',
            'telegram_notification_id' => 'Internal chat ID, if notified',
            'matrix_notification_id' => 'Internal chat ID, if notified',
            'updated_at' => 'user does not need to know when we addressed their report',
        ],
        'sessions' => [
            'id' => 'Internal integer PK; not relevant to the user',
            'payload' => 'Contains the session token, which is a secret that must not be exported',
        ],
        'webhook_creation_requests' => [
            'url' => 'Private of the app owner',
        ],
        'webhook_call_logs' => [
            'url' => 'Private of the app owner',
            'response_code' => 'Only for internal purposes',
        ],
    ];

    /**
     * User model relation methods intentionally not covered by any exporter.
     * Format: 'relationMethodName' => 'justification'
     *
     * Relations that merely alias or duplicate another exported relation should be
     * listed here so they are not flagged as gaps.
     */
    private const INTENTIONALLY_EXCLUDED_RELATIONS = [
        'blockedByUsers' => "Data belongs to other users, not the subject's own personal data",
        'trustedByUsers' => "Data belongs to other users, not the subject's own personal data",
        'clients' => 'Alias for oAuthClients — covered by AppsExporter',
        'oauthApps' => 'Alias for oAuthClients — covered by AppsExporter',
        'userFollowings' => 'Pivot alias for followings — covered by FollowsExporter',
        'userFollowers' => 'Pivot alias for followers — covered by FollowingsExporter',
        'userFollowRequests' => 'Pivot alias for followRequests — covered by FollowsRequestsExporter',
    ];

    private const MANUAL_RELATION_EXPORTER = [
        'statuses' => StatusExporter::class,
        'trainCheckins' => StatusExporter::class,
    ];

    /**
     * Model classes whose tables have a user_id column but are intentionally not exported.
     * Format: 'App\Models\Foo' => 'justification'
     */
    private const INTENTIONALLY_EXCLUDED_MODELS = [
        YearInReviewCache::class => 'Derived/cached statistics, regeneratable from exported data',
    ];

    private const MANUAL_MODEL_EXPORTERS = [
        Status::class => StatusExporter::class,
        Checkin::class => StatusExporter::class,
    ];

    private int $criticalCount = 0;

    private int $warningCount = 0;

    private int $noticeCount = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->info('┌──────────────────────────────────────────────────┐');
        $this->info('│            GDPR Export Audit                     │');
        $this->info('└──────────────────────────────────────────────────┘');

        // Wrap everything in a rolled-back transaction so relation calls that have
        // side effects (e.g. socialProfile() auto-creates a record) don't modify the DB.
        DB::beginTransaction();
        try {
            $exporters = $this->loadRegisteredExporters();
            $coveredTables = $this->buildCoverageMap($exporters);

            $this->section1ExporterColumns($exporters);
            $this->section2UserRelations($exporters, $coveredTables);
            $this->section3ModelsWithUserId($coveredTables);
        } finally {
            DB::rollBack();
        }

        $this->newLine();
        $this->line(self::DIVIDER);

        if ($this->criticalCount === 0 && $this->warningCount === 0 && $this->noticeCount === 0) {
            $this->info('✓ No issues found — GDPR export appears complete.');
        } else {
            $this->line(sprintf(
                '  %s  |  %s  |  %s',
                $this->criticalCount > 0
                    ? "<fg=red;options=bold>{$this->criticalCount} CRITICAL</>"
                    : '<fg=green>0 CRITICAL</>',
                $this->warningCount > 0
                    ? "<fg=yellow>{$this->warningCount} WARNING</>"
                    : '<fg=green>0 WARNING</>',
                $this->noticeCount > 0
                    ? "<fg=cyan>{$this->noticeCount} NOTICE</>"
                    : '<fg=green>0 NOTICE</>',
            ));
        }

        $this->newLine();

        $strict = (bool) $this->option('strict');

        return ($this->criticalCount > 0 || ($strict && $this->warningCount > 0)) ? 1 : 0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Section 1 — Exporter column coverage
    // ──────────────────────────────────────────────────────────────────────────

    /** @param array<string, ReflectionClass> $exporters */
    private function section1ExporterColumns(array $exporters): void
    {
        $this->newLine();
        $this->info('Section 1: Exporter Column Coverage');
        $this->line(self::DIVIDER);

        $dummyUser = (new User())->forceFill(['id' => 999_999_999, 'email' => 'gdpr-audit@example.com', 'username' => '_audit']);

        foreach ($exporters as $name => $reflection) {
            $traits = $this->traitShortNames($reflection);
            $fileName = $this->getProp($reflection, 'fileName') ?? '?';

            $this->newLine();
            $this->line("  <fg=cyan;options=bold>[{$name}]</> → {$fileName}");

            if (in_array('DatabaseExportable', $traits, true)) {
                $this->checkDatabaseExporter($name, $reflection);
            } elseif (in_array('RelationExportable', $traits, true)) {
                $this->checkRelationExporter($name, $reflection, $dummyUser);
            } elseif (in_array('ModelExportable', $traits, true)) {
                $this->checkModelExporter($name, $reflection);
            } elseif ($name === 'UserDataExporter') {
                $this->checkUserDataExporter($reflection);
            } else {
                // Custom exporter without a standard trait — compare $columns if defined.
                $columns = $this->getProp($reflection, 'columns');
                if ($columns === null) {
                    $this->notice('    Custom exporter without $columns — manual review required.');
                } else {
                    $this->line('    Custom exporter with explicit $columns — no automated schema check possible.');
                }
            }
        }
    }

    private function checkDatabaseExporter(string $name, ReflectionClass $reflection): void
    {
        $table = $this->getProp($reflection, 'tableName');
        $columns = $this->getProp($reflection, 'columns') ?? [];

        if (!$table) {
            $this->critical('    $tableName not set.');

            return;
        }

        $this->line("    DB table: <fg=yellow>{$table}</>");

        if (!Schema::hasTable($table)) {
            $this->critical("    Table '{$table}' does not exist in the database.");

            return;
        }

        $this->compareColumnsToSchema($table, $columns);
    }

    private function checkRelationExporter(string $name, ReflectionClass $reflection, User $dummyUser): void
    {
        $relation = $this->getProp($reflection, 'relation');
        $columns = $this->getProp($reflection, 'columns');

        if (!$relation) {
            $this->critical('    $relation not set.');

            return;
        }

        if (!method_exists($dummyUser, $relation)) {
            $this->critical("    Relation '{$relation}' does not exist on User model.");

            return;
        }

        try {
            $relObj = $dummyUser->{$relation}();
            // For column validation we always check against the related model's own table,
            // because ->select($columns)->get() fetches attributes from that table.
            $table = $relObj->getRelated()->getTable();
        } catch (Throwable $e) {
            $this->notice("    Could not resolve relation '{$relation}': {$e->getMessage()}");

            return;
        }

        $this->line("    User->{$relation}() → table: <fg=yellow>{$table}</>");

        if ($columns === null) {
            $this->warning('    No $columns defined — exports all fields. Verify no sensitive columns are included.');

            return;
        }

        if (!Schema::hasTable($table)) {
            $this->critical("    Resolved table '{$table}' does not exist.");

            return;
        }

        $this->compareColumnsToSchema($table, $columns);
    }

    private function checkModelExporter(string $name, ReflectionClass $reflection): void
    {
        $modelClass = $this->getProp($reflection, 'model');
        $columns = $this->getProp($reflection, 'columns');

        if (!$modelClass || !class_exists($modelClass)) {
            $this->critical("    Model class '{$modelClass}' not found.");

            return;
        }

        $table = (new $modelClass())->getTable();
        $this->line("    Model: <fg=yellow>{$modelClass}</> → table: <fg=yellow>{$table}</>");

        if ($columns === null) {
            $this->warning('    No $columns defined — exports all fields. Verify no sensitive columns are included.');

            return;
        }

        if (!Schema::hasTable($table)) {
            $this->critical("    Table '{$table}' does not exist.");

            return;
        }

        $this->compareColumnsToSchema($table, $columns);
    }

    private function checkUserDataExporter(ReflectionClass $reflection): void
    {
        $columns = $this->getProp($reflection, 'columns') ?? [];
        $table = 'users';

        $this->line("    DB table: <fg=yellow>{$table}</> (direct model->only())");

        if (!Schema::hasTable($table)) {
            $this->critical("    Table '{$table}' does not exist.");

            return;
        }

        $this->compareColumnsToSchema($table, $columns);
    }

    /**
     * Compare an exporter's column list against the actual DB schema.
     * Reports phantom columns (typos) and columns present in DB but missing from export.
     *
     * @param  string[]  $exporterColumns
     */
    private function compareColumnsToSchema(string $table, array $exporterColumns): void
    {
        $dbColumns = Schema::getColumnListing($table);
        $excluded = array_keys(self::INTENTIONALLY_EXCLUDED_COLUMNS[$table] ?? []);

        $phantom = array_values(array_diff($exporterColumns, $dbColumns));
        foreach ($phantom as $col) {
            $this->critical(
                "    Column '<fg=white;options=bold>{$col}</>' is in the exporter but NOT in table '{$table}'" .
                ' — check for typo (column will be silently ignored at runtime).'
            );
        }

        $uncovered = array_values(array_diff($dbColumns, $exporterColumns, $excluded));
        foreach ($uncovered as $col) {
            $this->warning(
                "    Column '<fg=white>{$col}</>' is in the DB but NOT exported." .
                ' Add to exporter or document in INTENTIONALLY_EXCLUDED_COLUMNS.'
            );
        }

        if (empty($phantom) && empty($uncovered)) {
            $this->line('    <fg=green>✓ All columns accounted for.</>');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Section 2 — User model relation coverage
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, ReflectionClass>  $exporters
     * @param  array<string, string>  $coveredTables  table → exporter name
     */
    private function section2UserRelations(array $exporters, array $coveredTables): void
    {
        $this->newLine();
        $this->info('Section 2: User Model Relation Coverage');
        $this->line(self::DIVIDER);
        $this->line('  Every public relation method on User that returns an Eloquent Relation:');
        $this->newLine();

        $dummyUser = (new User())->forceFill(['id' => 999_999_999, 'email' => 'gdpr-audit@example.com', 'username' => '_audit']);
        $reflUser = new ReflectionClass(User::class);
        $relMethods = $this->collectRelationMethods($reflUser);

        foreach ($relMethods as $method) {
            $methodName = $method->getName();

            if (array_key_exists($methodName, self::INTENTIONALLY_EXCLUDED_RELATIONS)) {
                $justification = self::INTENTIONALLY_EXCLUDED_RELATIONS[$methodName];
                $this->line("  <fg=green>✓</> {$methodName}() — excluded: {$justification}");

                continue;
            }

            if (array_key_exists($methodName, self::MANUAL_RELATION_EXPORTER)) {
                $exporter = self::MANUAL_RELATION_EXPORTER[$methodName];
                $this->line(
                    "  <fg=cyan>✓</> {$methodName}() — covered by {$exporter} (manual check)"
                );
                $this->notice('verify that the exporter fully covers this model’s personal data.');

                continue;
            }

            try {
                $relObj = $dummyUser->{$methodName}();
            } catch (Throwable $e) {
                $this->notice("  ? {$methodName}() — could not resolve: {$e->getMessage()}");

                continue;
            }

            // For BelongsToMany, check both the pivot table (used by DatabaseExportable exporters
            // like FollowsExporter) and the related model's table (used by RelationExportable
            // exporters like RoleExporter), because different BelongsToMany relations export
            // different things.
            $tablesToCheck = $relObj instanceof BelongsToMany
                ? [$relObj->getTable(), $relObj->getRelated()->getTable()]
                : [$relObj->getRelated()->getTable()];

            $matchedTable = null;
            $matchedExporter = null;
            foreach ($tablesToCheck as $t) {
                if (isset($coveredTables[$t])) {
                    $matchedTable = $t;
                    $matchedExporter = $coveredTables[$t];
                    break;
                }
            }

            if ($matchedExporter !== null) {
                $this->line("  <fg=green>✓</> {$methodName}() → <fg=yellow>{$matchedTable}</> (covered by {$matchedExporter})");
            } else {
                $table = $tablesToCheck[0];
                $this->critical(
                    "  Relation <fg=white;options=bold>{$methodName}()</> → table <fg=yellow>{$table}</>" .
                    ' is NOT covered by any exporter.'
                );
            }
        }
    }

    /** @return ReflectionMethod[] */
    private function collectRelationMethods(ReflectionClass $reflUser): array
    {
        $result = [];

        foreach ($reflUser->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== User::class) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (!($returnType instanceof ReflectionNamedType)) {
                continue;
            }

            $typeName = $returnType->getName();
            if (!class_exists($typeName) || !is_subclass_of($typeName, Relation::class)) {
                continue;
            }

            $result[] = $method;
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Section 3 — All models with a user_id column
    // ──────────────────────────────────────────────────────────────────────────

    /** @param array<string, string> $coveredTables */
    private function section3ModelsWithUserId(array $coveredTables): void
    {
        $this->newLine();
        $this->info('Section 3: Models with user_id Column (Broad Coverage Check)');
        $this->line(self::DIVIDER);
        $this->line('  Every Model in app/Models that has a user_id column:');
        $this->newLine();

        $modelFiles = glob(app_path('Models/*.php')) ?: [];

        foreach ($modelFiles as $file) {
            $shortName = basename($file, '.php');
            $fqcn = 'App\\Models\\' . $shortName;

            if (!class_exists($fqcn)) {
                continue;
            }

            try {
                $instance = new $fqcn();
            } catch (Throwable) {
                continue;
            }

            if (!($instance instanceof Model)) {
                continue;
            }

            $table = $instance->getTable();

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'user_id')) {
                continue;
            }

            if (array_key_exists($fqcn, self::INTENTIONALLY_EXCLUDED_MODELS)) {
                $justification = self::INTENTIONALLY_EXCLUDED_MODELS[$fqcn];
                $this->line("  <fg=yellow>⊘</> {$shortName} (<fg=yellow>{$table}</>) — excluded: {$justification}");

                continue;
            }

            if (array_key_exists($fqcn, self::MANUAL_MODEL_EXPORTERS)) {
                $exporter = self::MANUAL_MODEL_EXPORTERS[$fqcn];
                $this->line(
                    "  <fg=cyan>✓</> {$shortName} (<fg=yellow>{$table}</>) — covered by {$exporter} (manual check)"
                );
                $this->notice('verify that the exporter fully covers this model’s personal data.');

                continue;
            }

            if (isset($coveredTables[$table])) {
                $exporter = $coveredTables[$table];
                $this->line("  <fg=green>✓</> {$shortName} (<fg=yellow>{$table}</>) — covered by {$exporter}");
            } else {
                $this->critical(
                    "  Model <fg=white;options=bold>{$shortName}</> (table: <fg=yellow>{$table}</>)" .
                    ' has a user_id column but is NOT exported.' .
                    ' Add an exporter or document in INTENTIONALLY_EXCLUDED_MODELS.'
                );
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Loader
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Parse UserGdprDataService to extract the registered exporter class list.
     *
     * @return array<string, ReflectionClass> shortName → ReflectionClass
     */
    private function loadRegisteredExporters(): array
    {
        $servicePath = app_path('Services/PersonalDataSelection/UserGdprDataService.php');
        $source = file_get_contents($servicePath);

        preg_match_all('/\b([A-Za-z]+Exporter)::class/', $source, $matches);

        $exporters = [];
        foreach (array_unique($matches[1]) as $shortName) {
            $fqcn = 'App\\Services\\PersonalDataSelection\\Exporters\\' . $shortName;
            if (!class_exists($fqcn)) {
                $this->critical("Exporter '{$fqcn}' referenced in UserGdprDataService but class not found.");

                continue;
            }
            if (!is_subclass_of($fqcn, AbstractExporter::class)) {
                $this->critical("'{$fqcn}' does not extend AbstractExporter.");

                continue;
            }
            $exporters[$shortName] = new ReflectionClass($fqcn);
        }

        return $exporters;
    }

    /**
     * Build a map of DB table name → exporter short name for quick coverage lookups.
     *
     * @param  array<string, ReflectionClass>  $exporters
     * @return array<string, string>
     */
    private function buildCoverageMap(array $exporters): array
    {
        $covered = [];
        $dummyUser = (new User())->forceFill(['id' => 999_999_999, 'email' => 'gdpr-audit@example.com', 'username' => '_audit']);

        foreach ($exporters as $name => $reflection) {
            $traits = $this->traitShortNames($reflection);

            try {
                if (in_array('DatabaseExportable', $traits, true)) {
                    $table = $this->getProp($reflection, 'tableName');
                    if ($table) {
                        $covered[$table] = $name;
                    }
                } elseif (in_array('RelationExportable', $traits, true)) {
                    $relation = $this->getProp($reflection, 'relation');
                    if ($relation && method_exists($dummyUser, $relation)) {
                        // RelationExportable exporters select from the related model's own table.
                        $table = $dummyUser->{$relation}()->getRelated()->getTable();
                        $covered[$table] = $name;
                    }
                } elseif (in_array('ModelExportable', $traits, true)) {
                    $modelClass = $this->getProp($reflection, 'model');
                    if ($modelClass && class_exists($modelClass)) {
                        $covered[(new $modelClass())->getTable()] = $name;
                    }
                } elseif ($name === 'UserDataExporter') {
                    $covered['users'] = $name;
                } elseif ($name === 'WebhookExporter') {
                    $covered[(new Webhook())->getTable()] = $name;
                }
            } catch (Throwable) {
                // Coverage mapping is best-effort; section 2 will report the gap.
            }
        }

        return $covered;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** @return string[] */
    private function traitShortNames(ReflectionClass $reflection): array
    {
        return array_map(
            static fn (ReflectionClass $t) => $t->getShortName(),
            $reflection->getTraits()
        );
    }

    /**
     * Return the DB table that a relation actually stores data in.
     * For BelongsToMany this is the pivot/junction table; for everything else
     * it is the related model's own table.
     */
    private function getRelationTable(Relation $rel): string
    {
        if ($rel instanceof BelongsToMany) {
            return $rel->getTable();
        }

        return $rel->getRelated()->getTable();
    }

    /**
     * Read a (private/protected) property's default value without instantiating the class.
     */
    private function getProp(ReflectionClass $reflection, string $name): mixed
    {
        if (!$reflection->hasProperty($name)) {
            return null;
        }

        return $reflection->getDefaultProperties()[$name] ?? null;
    }

    private function critical(string $message): void
    {
        $this->criticalCount++;
        $this->line("  <fg=red;options=bold>[CRITICAL]</> {$message}");
    }

    private function warning(string $message): void
    {
        $this->warningCount++;
        $this->line("  <fg=yellow>[WARNING]</> {$message}");
    }

    private function notice(string $message): void
    {
        $this->noticeCount++;
        $this->line("  <fg=cyan>[NOTICE]</> {$message}");
    }
}
