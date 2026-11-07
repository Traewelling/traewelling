<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\MonitoredCallWebhookJob;
use Illuminate\Support\Str;
use Tests\Unit\UnitTestCase;

class MonitoredCallWebhookJobTest extends UnitTestCase
{
    public function test_webhook_requests_do_not_follow_redirects(): void
    {
        $job = $this->makeJob('https://example.com/webhook');

        $options = $this->requestOptions($job);

        $this->assertFalse($options['allow_redirects']);
    }

    public function test_request_options_keep_the_parent_defaults(): void
    {
        $job = $this->makeJob('https://example.com/webhook');
        $job->verifySsl = true;
        $job->proxy = 'tcp://proxy.example.com:8080';

        $options = $this->requestOptions($job);

        $this->assertTrue($options['verify']);
        $this->assertFalse($options['cookies']);
        $this->assertSame('tcp://proxy.example.com:8080', $options['proxy']);
    }

    public function test_hostnames_are_pinned_to_the_validated_address(): void
    {
        $job = $this->makeJob('https://example.com:8443/webhook');
        $this->setTargetAddresses($job, ['93.184.216.34', '93.184.216.35']);

        $options = $this->requestOptions($job);

        $this->assertSame(
            ['example.com:8443:93.184.216.34'],
            $options['curl'][CURLOPT_RESOLVE]
        );
    }

    public function test_hostnames_without_port_are_pinned_to_the_default_port(): void
    {
        $job = $this->makeJob('https://example.com/webhook');
        $this->setTargetAddresses($job, ['93.184.216.34']);

        $options = $this->requestOptions($job);

        $this->assertSame(
            ['example.com:443:93.184.216.34'],
            $options['curl'][CURLOPT_RESOLVE]
        );
    }

    public function test_ip_literal_targets_are_not_pinned(): void
    {
        $job = $this->makeJob('https://8.8.8.8/webhook');
        $this->setTargetAddresses($job, ['8.8.8.8']);

        $options = $this->requestOptions($job);

        $this->assertArrayNotHasKey(CURLOPT_RESOLVE, $options['curl'] ?? []);
    }

    private function makeJob(string $url): MonitoredCallWebhookJob
    {
        $job = new MonitoredCallWebhookJob();
        $job->webhookUrl = $url;
        $job->httpVerb = 'post';
        $job->proxy = null;
        $job->cert = null;
        $job->certPassphrase = null;
        $job->sslKey = null;
        $job->sslKeyPassphrase = null;
        $job->verifySsl = true;
        $job->payload = [];
        $job->headers = [];
        $job->meta = [];
        $job->tags = [];
        $job->uuid = (string) Str::uuid();

        return $job;
    }

    private function requestOptions(MonitoredCallWebhookJob $job): array
    {
        return \Closure::bind(
            fn (): array => $this->requestOptions(),
            $job,
            MonitoredCallWebhookJob::class
        )();
    }

    private function setTargetAddresses(MonitoredCallWebhookJob $job, array $addresses): void
    {
        \Closure::bind(
            function () use ($addresses): void {
                $this->targetAddresses = $addresses;
            },
            $job,
            MonitoredCallWebhookJob::class
        )();
    }
}
