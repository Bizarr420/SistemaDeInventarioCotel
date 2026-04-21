<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\ObsoletenessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckObsoleteness implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $service = new ObsoletenessService();

        Product::chunkById(100, function ($products) use ($service) {
            foreach ($products as $product) {
                $service->apply($product);
            }
        });
    }
}