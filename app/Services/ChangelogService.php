<?php

namespace App\Services;

use App\Dto\ChangelogEntryDto;
use App\Dto\ChangelogItemDto;
use App\Http\Controllers\Backend\VersionController;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ChangelogService
{
    private function fetchChangelog(): array
    {
        return cache()->remember('changelog', now()->addHour(), function () {
            return Http::withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2026-03-10'])
                ->get('https://api.github.com/repos/Traewelling/traewelling/releases')
                ->json();
        });
    }

    public function getChangelog(): array
    {
        $data = cache()->remember('changelog_parsed', now()->addHour(), function () {
            return $this->parseChangelog();
        });

        return $this->getDto($data);
    }

    /**
     * @return ChangelogEntryDto[]
     *
     * @throws ConnectionException
     */
    private function parseChangelog(): array
    {
        $changelog = $this->fetchChangelog();

        // prevent changelog from showing future versions
        $currentVersion = VersionController::getVersion();
        $head = $changelog[0];
        if (str_contains($currentVersion, '.') && $currentVersion !== $head['tag_name']) {
            while ($currentVersion !== $head['tag_name']) {
                $head = array_shift($changelog);
            }
            array_unshift($changelog, $head);
        }

        $parsed = [];
        foreach ($changelog as $change) {
            $shortenedBody = preg_replace('/(https:\/\/github\.com\/Traewelling\/traewelling\/pull\/(\d+))/', '[#$2]($1)', $change['body'] ?? '');
            $shortenedBody = preg_replace('/(https:\/\/github\.com\/Traewelling\/traewelling\/compare\/([\d\.]+))/', '[$2]($1)', $shortenedBody);
            $shortenedBody = preg_replace('/(@(\w+(?:\[bot\])?))/', '[$1](https://github.com/$2)', $shortenedBody);

            $lines = explode("\n", $shortenedBody);
            $features = array_filter($lines, fn ($line) => str_starts_with(trim($line), '* '));
            $entries = collect();
            foreach ($features as $feature) {
                $line = explode(' ', trim($feature)) ?? '';

                $type = str_replace("\n", '', strip_tags(markdown($line[1])));
                $shortenedLine = implode(' ', array_slice($line, 2, count($line) - 6));

                $entries->push([
                    'markdownLine' => $feature,
                    'type' => $type,
                    'shortenedLine' => $shortenedLine,
                ]);
            }

            $entries = $entries->sortBy('type')->toArray();

            $parsed[] = [
                'tag' => $change['tag_name'],
                'title' => $change['name'] ?? $change['tag_name'],
                'body' => $shortenedBody,
                'features' => $entries,
                'created' => $change['updated_at'] ?? $change['created_at'],
            ];
        }

        return $parsed;
    }

    private function getDto(array $changelog): array
    {
        $data = [];
        foreach ($changelog as $change) {
            $data[] = new ChangelogEntryDto(
                tag: $change['tag'],
                title: $change['title'],
                description: $change['body'],
                entries: array_map(fn ($entry) => new ChangelogItemDto(
                    markdownLine: $entry['markdownLine'],
                    type: $entry['type'],
                    shortenedLine: $entry['shortenedLine']
                ), $change['features']),
                created: Carbon::parse($change['created'])
            );
        }

        return $data;
    }
}
