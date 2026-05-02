<?php

namespace Modules\CommunicationModule\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CommunicationModule\Services\V1\IntegrationService;

class ProcessIntegrationWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $provider,
        public array $payload
    ) {
    }

    public function handle(IntegrationService $integrationService): void
    {
        $integrationService->processWebhook($this->provider, $this->payload);
    }
}
