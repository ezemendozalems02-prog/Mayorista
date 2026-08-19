<?php

namespace App\Jobs;

use App\Services\StockImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $mapping;
    protected $organizationId;
    protected $branchId;
    protected $disk;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $mapping, $organizationId, $branchId = null, $disk = 'local')
    {
        $this->filePath = $filePath;
        $this->mapping = $mapping;
        $this->organizationId = $organizationId;
        $this->branchId = $branchId;
        $this->disk = $disk;
    }

    /**
     * Execute the job.
     */
    public function handle(StockImportService $importService): void
    {
        $importService->performImport(
            $this->filePath,
            $this->mapping,
            $this->organizationId,
            $this->branchId,
            $this->disk
        );
    }
}
