<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use App\Http\Traits\RetryFtc;
use Log;
use App\Http\Traits\FastTaxAPI; 

class ProcessFtcOrders extends Command
{
    use RetryFtc, FastTaxAPI;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:ftc-orders {orderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process FTC orders for the given order IDs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderIds = (array) $this->argument('orderId');

        foreach ($orderIds as $orderId) {
            try {
                Log::info("Processing FTC Order: $orderId");
                $this->processFtcOrder($orderId); // Call the trait method
                // Log::info("Successfully processed FTC Order: $orderId");
            } catch (\Exception $e) {
                Log::error("Error processing FTC Order: $orderId - " . $e->getMessage());
            }
        }

        return 0;
    }
}
