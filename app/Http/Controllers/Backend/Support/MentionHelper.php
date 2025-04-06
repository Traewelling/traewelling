<?php declare(strict_types=1);

namespace App\Http\Controllers\Backend\Support;

use App\Dto\MentionDto;
use App\Http\Controllers\Backend\Social\MastodonProfileDetails;
use App\Models\Mention;
use App\Models\Status;
use App\Models\User;
use App\Notifications\UserMentioned;

class MentionHelper
{

    private ?string $body;
    private Status  $status;
    private bool    $isCreating;

    public function __construct(Status $status, ?string $body = null) {
        $this->status = $status;
        $status->load('mentions', 'mentions.mentioned');
        $this->body       = $body ?? $status->body;
        $this->isCreating = $body === null;
    }

    public static function findMentionsInString(string $string): array {
        preg_match_all('/@\w+/', $string, $matches, PREG_OFFSET_CAPTURE);

        return $matches[0];
    }

    /**
     * @return MentionDto[]
     */
    public function findUsersInString(): array {
        $users   = [];
        $matches = self::findMentionsInString($this->body ?? '');
        foreach ($matches as $match) {
            $user = User::where('username', substr($match[0], 1))->first();

            if ($user) {
                $users[] = new MentionDto($user, $match[1], strlen($match[0]));
            }
        }

        return $users;
    }

    public static function createMentions(Status $status, ?string $string = null): void {
        $self = new self($status, $string);
        $self->parseAndCreate();
    }

    private function parseAndCreate(): void {
        if ($this->body === $this->status->body && !$this->isCreating) {
            return;
        }
        if (empty($this->body)) {
            $this->status->mentions()->delete();
            return;
        }
        $newMentions = $this->findUsersInString();

        // compare old mentions with new mentions and delete old mentions that are not in the new mentions
        $oldMentions = $this->status->mentions;
        foreach ($oldMentions as $oldMention) {
            $found = false;
            foreach ($newMentions as $key => $newMention) {
                if (
                    $oldMention->mentioned_id === $newMention->user->id
                    && $oldMention->position === $newMention->position
                    && $oldMention->length === $newMention->length
                ) {
                    $found = true;
                    unset($newMentions[$key]);
                    break;
                }
            }
            if (!$found) {
                $oldMention->delete();
            }
        }

        foreach ($newMentions as $newMention) {
            $mention = Mention::fromMentionDto($newMention, $this->status);
            $mention->save();

            $this->sendNotification($mention);
        }

    }

    private function sendNotification(Mention $mention): void {
        if ($mention->mentioned->id === $this->status->user_id) {
            return;
        }
        if ($mention->mentioned->cannot('view', $this->status)) {
            return;
        }

        $found = false;
        // only send notification if the user has not been mentioned before
        foreach ($this->status->mentions as $oldMention) {
            if ($oldMention->mentioned_id === $mention->mentioned_id) {
                $found = true;
            }
        }
        if (!$found) {
            $mention->mentioned->notify(new UserMentioned($mention));
        }
    }


    public static function getBodyWithMentionLinks(Status $status): string {
        $body     = htmlspecialchars($status->body);
        $replaced = [];
        $mentions = $status->mentions;
        foreach ($mentions as $mention) {
            $user = $mention->mentioned;
            if (in_array($user->username, $replaced)) {
                continue;
            }
            $body       = strtr($body, [
                "@$user->username" =>
                    '<a href="' . route('profile', $user->username) . '">@' . $user->username . '</a>'
            ]);
            $replaced[] = $user->username;
        }
        return $body;
    }

    public static function getMastodonStatus(Status $status): string {
        $body = $status->body;
        if (empty($body)) {
            return '';
        }

        $replaced = [];
        $mentions = $status->mentions;
        foreach ($mentions as $mention) {
            $user = $mention->mentioned;
            if ($user->socialProfile?->mastodon_id === null || in_array($user->username, $replaced)) {
                continue;
            }

            $mastodonHelper = new MastodonProfileDetails($user);
            $username       = '@' . $mastodonHelper->getUserName() . '@' . $mastodonHelper->getProfileHost();

            $body       = strtr($body, ["@$user->username" => $username]);
            $replaced[] = $user->username;
        }
        return $body;
    }
}
