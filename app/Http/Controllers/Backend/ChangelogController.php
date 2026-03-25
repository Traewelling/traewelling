<?php

namespace App\Http\Controllers\Backend;

use App\Dto\ChangelogEntryDto;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ChangelogController
{
    /**
     * @return ChangelogEntryDto[]
     *
     * @throws ConnectionException
     */
    public function getChangelog(): array
    {
        $changelog = cache()->remember('changelog', now()->addHour(), function () {
            return Http::withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2026-03-10'])
                ->get('https://api.github.com/repos/Traewelling/traewelling/releases')
                ->json();
        });

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
            $shorenedBody = preg_replace('/(https:\/\/github\.com\/Traewelling\/traewelling\/pull\/(\d+))/', '[#$2]($1)', $change['body'] ?? '');
            $shorenedBody = preg_replace('/(https:\/\/github\.com\/Traewelling\/traewelling\/compare\/([\d\.]+))/', '[$2]($1)', $shorenedBody);
            $shorenedBody = preg_replace('/(@(\w+(?:\[bot\])?))/', '[$1](https://github.com/$2)', $shorenedBody);

            $parsed[] = new ChangelogEntryDto(
                tag: $change['tag_name'],
                title: $change['name'] ?? $change['tag_name'],
                description: $shorenedBody,
                created: Carbon::parse($change['updated_at'] ?? $change['created_at']),
            );
        }

        return $parsed;
    }
}
