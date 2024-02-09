<?php declare(strict_types=1);

namespace App\Http\Controllers\Backend\Support;

use App\Dto\MentionDto;
use App\Models\Mention;
use App\Models\Status;
use App\Models\User;

class MentionHelper
{

    private ?string $body;
    private Status  $status;
    private bool    $isCreating;

    public function __construct(Status $status, string $body = null) {
        $this->status     = $status;
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
    private function findUsersInString(): array {
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

    public static function createMentions(Status $status, string $string = null): void {
        $self = new self($status, $string);
        $self->parseAndCreate();
    }

    private function parseAndCreate(): void {
        if (empty($this->body) || ($this->body === $this->status->body && !$this->isCreating)) {
            return;
        }
        $newMentions = $this->findUsersInString();

        // compare old mentions with new mentions and delete old mentions that are not in the new mentions
        $oldMentions = $this->status->mentions;
        foreach ($oldMentions as $oldMention) {
            $found = false;
            foreach ($newMentions as $key => $newMention) {
                if (
                    $oldMention->mentioned->id === $newMention->user->id
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
            $body       = strtr($body, ["@{$user->username}" => "<a href=\"/@{$user->username}\">@$user->username</a>"]);
            $replaced[] = $user->username;
        }
        return $body;
    }

}
