<?php

namespace App\Jobs;

use App\DataTransferObjects\UploadCsvData;
use App\Services\ProductService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProductsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public UploadCsvData $dto
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ProductService $service)
    {
        $service->erase();
        $service->importCsv($this->dto);
    }
}
