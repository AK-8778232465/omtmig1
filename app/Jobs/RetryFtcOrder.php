<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\FastTaxAPI;
use Illuminate\Support\Facades\Http;

class RetryFtcOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;
    protected $ftcOrderId;

    /**
     * Create a new job instance.
     *
     * @param int $orderId
     * @param int $ftcOrderId
     * @return void
     */
    public function __construct($orderId, $ftcOrderId)
    {
        $this->orderId = $orderId;
        $this->ftcOrderId = $ftcOrderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch the FTC order data
        $ftcOrder = DB::table('ftc_order_data')->where('order_id', $this->orderId)->first();

        if (!$ftcOrder) {
            // If the order does not exist, stop processing
            return;
        }

        // Prepare the data to call the FTC API
        $data = ["OrderId" => $ftcOrder->ftc_order_id];

        
            $ftcResponse = $this->getFtcData('ftc/GetOrderStatusFTC.php', $data);
       
            if (empty($ftcResponse)) {
                \Log::error("FTC API returned an empty response", ['order_id' => $this->orderId]);
                return; // Exit or handle as needed
            }
            
            if (!is_array($ftcResponse)) {
                \Log::error("FTC API response is not an array", ['response' => $ftcResponse]);
                return; // Exit or handle as needed
            }
     


      
        // Check if the response is still in progress or null
        if ($ftcResponse['result'] === null || (isset($ftcResponse['Status']) && $ftcResponse['Status'] == "In Progress")) {
            // Retry the job after a delay (e.g., 5 minutes)
            RetryFtcOrder::dispatch($this->orderId, $this->ftcOrderId)->delay(now()->addMinutes(5));
            return;
        }

       
        if (!is_null($ftcResponse['result']) && $ftcResponse['Status'] != "In Progress" ) {

                    DB::table('ftc_order_data')
                            ->where('id',$ftc_order->id)
                            ->update([
                                'ftc_response' =>$ftcResponse['result'],
                                'ftc_status' => $ftcResponse['Status'],
                                'updated_at'=> Carbon::now(),
                            ]); 
                            
                    DB::table('taxes')
                            ->where('id',$ftc_order->order_id)
                            ->update([
                                'json' =>$ftcResponse['result'],
                            ]);             
                            return response()->json(['message' => 'Order successfully fetch.'], 200);

                    try {
                        $support_files = json_decode($ftcResponse['supportfiles'], true);
                        $fileCount = count($support_files['fileslist']);
                        foreach($support_files['fileslist'] as $filelist)
                        {
                            $decodedData = base64_decode($filelist['file']);
                            $filename = uniqid().' '.$filelist['file_name'];
                            \Storage::put("taxcert/{$ftc_order->order_id}/$filename", $decodedData);
                            SupportingDocs::insertGetId(['order_id' => $ftc_order->order_id,'pdf_file' => $filename,'created_at' => now()]);
                            if(substr(PHP_OS, 0, 3) != 'WIN') {
                                $this->changeFolderPermissions("taxcert/{$ftc_order->order_id}/$filename", 0777);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error while processing documents: {$e->getMessage()}");
                    }
    

                } else {
                    // Log or handle the case where result is still null after retrying
                    // ...
                }
    }

    /**
     * Fetch FTC data (mock or actual API call).
     *
     * @param string $url
     * @param array $data
     * @return array
     */
    private function getFtcData($url, $data)
    {
        // Your logic to fetch data from the FTC API (mock or actual)
        return [];  // Return mock data or actual API response
    }
}
