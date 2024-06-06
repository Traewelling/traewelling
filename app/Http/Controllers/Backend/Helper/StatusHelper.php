<?php

namespace App\Http\Controllers\Backend\Helper;

use App\Http\Controllers\Backend\Support\MentionHelper;
use App\Models\Status;

class StatusHelper
{
    private Status $status;
    private bool   $mastodon;

    public function __construct(Status $status, bool $mastodon = false) {
        $this->status   = $status;
        $this->mastodon = $mastodon;
    }

    public static function getSocialText(Status $status, bool $mastodon = false): string {
        $self = new self($status, $mastodon);
        return $self->generateSocialText();
    }

    private function generateEventText(): string {
        return trans_choice(
            key:     'controller.transport.social-post-with-event',
            number:  preg_match('/\s/', $this->status->checkin->trip->linename),
            replace: [
                         'lineName'    => $this->status->checkin->trip->linename,
                         'destination' => $this->status->checkin->destinationStopover->station->name,
                         'hashtag'     => $this->status->event->hashtag
                     ]
        );
    }

    private function generateBaseText(): string {
        return trans_choice(
            key:     'controller.transport.social-post',
            number:  preg_match('/\s/', $this->status->checkin->trip->linename),
            replace: [
                         'lineName'    => $this->status->checkin->trip->linename,
                         'destination' => $this->status->checkin->destinationStopover->station->name
                     ]
        );
    }

    private function generateAppendix(): string {
        if ($this->status->event?->hashtag !== null) {
            $eventIntercept = __('controller.transport.social-post-for', [
                'hashtag' => $this->status->event->hashtag
            ]);
        }

        return strtr(' (@ :linename ➜ :destination:eventIntercept) #NowTräwelling', [
            ':linename'       => $this->status->checkin->trip->linename,
            ':destination'    => $this->status->checkin->destinationStopover->station->name,
            ':eventIntercept' => isset($eventIntercept) ? ' ' . $eventIntercept : ''
        ]);
    }

    public function getMastodonBody(): string {
        return MentionHelper::getMastodonStatus($this->status);
    }

    private function generateSocialText(): string {
        if (isset($this->status->body)) {
            $body           = $this->mastodon ? $this->getMastodonBody() : $this->status->body;
            $appendix       = $this->generateAppendix();
            $appendixLength = strlen($appendix) + 30;
            $postText       = substr($body, 0, 500 - $appendixLength);
            if (strlen($postText) !== strlen($body)) {
                $postText .= '…';
            }
            $postText .= $appendix;

            return $postText;
        }


        if (isset($this->status->event) && $this->status->event->hashtag !== null) {
            $postText = $this->generateEventText();
        } else {
            $postText = $this->generateBaseText();
        }
        return $postText;
    }
}
