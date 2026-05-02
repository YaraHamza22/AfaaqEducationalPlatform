<?php

namespace Modules\ReportingModule\Console\Commands;

use Illuminate\Console\Command;
use Modules\ReportingModule\Services\SnapshotMaterializationService;
use Throwable;

class MaterializeSnapshotsCommand extends Command
{
    protected $signature = 'reporting:snapshots:materialize {--date= : Snapshot date (Y-m-d)}';

    protected $description = 'Materialize reporting snapshots into read-model tables.';

    public function __construct(private SnapshotMaterializationService $materializationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $result = $this->materializationService->materialize($this->option('date'));

            $this->info('Reporting snapshots materialized successfully.');
            $this->line('Snapshot date: '.$result['snapshot_date']);
            $this->line('Assessment progress rows: '.$result['assessment_progress_rows']);
            $this->line('Certificate funnel rows: '.$result['certificate_funnel_rows']);
            $this->line('Engagement activity rows: '.$result['engagement_activity_rows']);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to materialize snapshots: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
