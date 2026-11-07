<?php

namespace App\Jobs;

use App\Exceptions\UntrustedWebhookTargetException;
use App\Rules\SecureWebhookUrl;
use romanzipp\QueueMonitor\Traits\IsMonitored;
use Spatie\WebhookServer\CallWebhookJob;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;

class MonitoredCallWebhookJob extends CallWebhookJob
{
    use IsMonitored;

    /** @var list<string>|null */
    protected ?array $targetAddresses = null;

    public function handle(): void
    {
        if ($this->webhookUrl !== null) {
            $this->targetAddresses = SecureWebhookUrl::resolveAllowedTargetAddresses($this->webhookUrl);

            if ($this->targetAddresses === null) {
                $this->errorType = UntrustedWebhookTargetException::class;
                $this->errorMessage = "The webhook target {$this->webhookUrl} is not allowed.";

                $this->dispatchFailedCallEvents();
                $this->delete();

                return;
            }
        }

        parent::handle();
    }

    protected function requestOptions(): array
    {
        $options = [
            ...parent::requestOptions(),
            'allow_redirects' => false,
        ];

        if ($this->targetAddresses !== null && $this->hostRequiresDnsPinning()) {
            // We have done the DNS query before and validated that it's not an internal IP as
            // described in SecureWebhookUrl::BLOCKED_RANGES.
            $options['curl'][CURLOPT_RESOLVE] = [$this->dnsPin()];
        }

        return $options;
    }

    private function hostRequiresDnsPinning(): bool
    {
        $host = trim((string) parse_url($this->webhookUrl, PHP_URL_HOST), '[]');

        return !filter_var($host, FILTER_VALIDATE_IP);
    }

    private function dnsPin(): string
    {
        $host = trim((string) parse_url($this->webhookUrl, PHP_URL_HOST), '[]');
        $port = parse_url($this->webhookUrl, PHP_URL_PORT) ?? 443;

        return sprintf('%s:%d:%s', $host, $port, $this->targetAddresses[0]);
    }

    private function dispatchFailedCallEvents(): void
    {
        $eventArguments = [
            $this->httpVerb,
            (string) $this->webhookUrl,
            $this->payload,
            $this->headers,
            $this->meta,
            $this->tags,
            $this->attempts(),
            $this->response,
            $this->errorType,
            $this->errorMessage,
            $this->uuid,
            $this->transferStats,
        ];

        event(new WebhookCallFailedEvent(...$eventArguments));
        event(new FinalWebhookCallFailedEvent(...$eventArguments));
    }
}
