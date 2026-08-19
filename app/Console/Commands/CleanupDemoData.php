<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cleanup {--hours=1 : The number of hours after which demo data is deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up demo data older than the specified hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $cutoffTime = Carbon::now()->subHours($hours);

        $demoOrganizations = Organization::where('is_demo', true)
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $count = 0;
        foreach ($demoOrganizations as $org) {
            /** @var Organization $org */
            // Because relationships should have onDelete cascade or we may need to manually delete
            // For safety, force delete demo users first
            $org->users()->forceDelete();
            
            // Delete the organization
            $org->forceDelete();
            $count++;
        }

        $this->info("Successfully deleted {$count} demo organizations older than {$hours} hours.");
        Log::info("CleanupDemoData executed: Deleted {$count} demo organizations older than {$hours} hours.");
    }
}
