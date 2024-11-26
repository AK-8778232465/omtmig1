<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\FastTaxAPI;
use Illuminate\Support\Facades\Http;
use DB;
use App\Models\SupportingDocs;
use Session;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use App\Models\OmsAttachmentHistory;


class RetryFtcOrder implements ShouldQueue
{
    use FastTaxAPI;
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
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ftcOrder = DB::table('ftc_order_data')->where('order_id', $this->orderId)->first();
   
        if (!$ftcOrder) {
            \Log::error("FTC order not found", ['order_id' => $this->orderId]);
            return;
        }
    
        $data = ["OrderId" => $ftcOrder->ftc_order_id];
        $ftcResponse = $this->getFtcData('ftc/GetOrderStatusFTC.php', $data );
    // dd($ftcOrder);
        if (empty($ftcResponse)) {
            \Log::error("Empty response from FTC API", ['order_id' => $this->orderId]);
            return;
        }
    
        if ($ftcResponse['result'] === null || (isset($ftcResponse['Status']) && $ftcResponse['Status'] == "In Progress")) {
            \Log::info("FTC response in progress", ['response' => $ftcResponse]);
            RetryFtcOrder::dispatch($this->orderId);
            return;
        }

        DB::beginTransaction();
        try {
            DB::table('ftc_order_data')
                ->where('id', $ftcOrder->id)
                ->update([
                    'ftc_response' => $ftcResponse['result'],
                    'ftc_status' => $ftcResponse['Status'],
                    'updated_at' => Carbon::now(),
                ]);
    
            \Log::info("FTC order data updated", ['order_id' => $this->orderId]);
    
            DB::table('taxes')->insert([
                'order_id' => $ftcOrder->order_id,
                'json' => $ftcResponse['result'],
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);
          
    
            \Log::info("Tax record inserted", ['order_id' => $this->orderId]);
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Database transaction failed", ['error' => $e->getMessage()]);
            return;
        }
    
        try {
            $supportFiles = json_decode($ftcResponse['supportfiles'], true);
            foreach ($supportFiles['fileslist'] as $file) {
                $decodedData = base64_decode($file['file']);
                $filename = uniqid() . '_' . $file['file_name'];
                $filePath = "taxcert/$filename";
    
                \Storage::disk('public')->put($filePath, $decodedData);
    
                SupportingDocs::insertGetId([
                    'order_id' => $ftcOrder->order_id,
                    'file_path' => $filePath,
                    'file_name' => $file['file_name'],
                    'created_at' => now(),
                ]);
                OmsAttachmentHistory::create([
                    'order_id' => $ftcOrder->order_id,
                    'updated_by' => Auth::id(),
                    'action' => 'Uploaded',
                    'file_name' => $file['file_name'],
                    'updated_at' => now(),
                ]);
    
                \Log::info("Supporting document saved", ['file' => $filename]);
            }
        } catch (\Exception $e) {
            \Log::error("Error while processing documents", ['error' => $e->getMessage()]);
        }
    }
    

    /**
     * Fetch FTC data (mock or actual API call).
     *
     * @param string $url
     * @param array $data
     * @return array
     */
    // private function getFtcData($url, $data)
    // {

     
    //     // Your logic to fetch data from the FTC API (mock or actual)
    //     return [];  // Return mock data or actual API response
    // }
}
