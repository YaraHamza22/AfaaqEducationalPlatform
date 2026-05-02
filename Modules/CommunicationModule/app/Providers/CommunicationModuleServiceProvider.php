<?php

namespace Modules\CommunicationModule\Providers;

use Illuminate\Support\Facades\Gate;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\CommunicationModule\Models\ChatMessage;
use Modules\CommunicationModule\Models\ChatThread;
use Modules\CommunicationModule\Models\ExternalIntegration;
use Modules\CommunicationModule\Models\ForumPost;
use Modules\CommunicationModule\Models\ForumThread;
use Modules\CommunicationModule\Models\OfflineDownloadToken;
use Modules\CommunicationModule\Models\OfflinePackage;
use Modules\CommunicationModule\Models\VirtualSession;
use Modules\CommunicationModule\Policies\ChatMessagePolicy;
use Modules\CommunicationModule\Policies\ChatThreadPolicy;
use Modules\CommunicationModule\Policies\ExternalIntegrationPolicy;
use Modules\CommunicationModule\Policies\ForumPostPolicy;
use Modules\CommunicationModule\Policies\ForumThreadPolicy;
use Modules\CommunicationModule\Policies\OfflineDownloadTokenPolicy;
use Modules\CommunicationModule\Policies\OfflinePackagePolicy;
use Modules\CommunicationModule\Policies\VirtualSessionPolicy;

class CommunicationModuleServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'CommunicationModule';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'communicationmodule';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(ChatThread::class, ChatThreadPolicy::class);
        Gate::policy(ChatMessage::class, ChatMessagePolicy::class);
        Gate::policy(ForumThread::class, ForumThreadPolicy::class);
        Gate::policy(ForumPost::class, ForumPostPolicy::class);
        Gate::policy(ExternalIntegration::class, ExternalIntegrationPolicy::class);
        Gate::policy(VirtualSession::class, VirtualSessionPolicy::class);
        Gate::policy(OfflinePackage::class, OfflinePackagePolicy::class);
        Gate::policy(OfflineDownloadToken::class, OfflineDownloadTokenPolicy::class);
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
