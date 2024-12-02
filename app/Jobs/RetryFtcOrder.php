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
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

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

            RetryFtcOrder::dispatch($this->orderId)->delay(now()->addSeconds(6));

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


            $existingTaxRecord = DB::table('taxes')->where('order_id', $ftcOrder->order_id)->first();

                if ($existingTaxRecord) {
                    // Step 2: Delete the existing record
                    DB::table('taxes')->where('order_id', $ftcOrder->order_id)->delete();
                }
    
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
            $filesToInsert = [];  // Initialize an array to hold the files for bulk insert
            $historyToInsert = [];  // Initialize an array to hold the history for bulk insert
        
            // Step 1: Clear existing history records for the order
            OmsAttachmentHistory::where('order_id', $ftcOrder->order_id)->delete();
        
            // Step 2: Fetch all existing SupportingDocs records for the order
            $existingRecords = SupportingDocs::where('order_id', $ftcOrder->order_id)->get();
        
            // Step 3: Delete all existing records and files
            foreach ($existingRecords as $existingRecord) {
                // Delete the file from storage
                if (Storage::exists($existingRecord->file_path)) {
                    Storage::delete($existingRecord->file_path);
                }
        
                // Delete the record from the database
                $existingRecord->delete();
                \Log::info("Existing supporting document and file deleted", ['order_id' => $ftcOrder->order_id, 'file_name' => $existingRecord->file_name]);
            }
        
            // Step 4: Process each new file
            foreach ($supportFiles['fileslist'] as $file) {
                $decodedData = base64_decode($file['file']);
                $filename = uniqid() . '_' . $file['file_name'];
                $filePath = "taxcert/$filename";
        
                // Step 5: Save the new file to storage
                \Storage::disk('public')->put($filePath, $decodedData);
        
                // Step 6: Prepare data for bulk insert of supporting documents
                $filesToInsert[] = [
                    'order_id' => $ftcOrder->order_id,
                    'file_path' => $filePath,
                    'file_name' => $file['file_name'],
                    'created_at' => now(),
                ];
        
                // Step 7: Prepare history data for bulk insert
                $historyToInsert[] = [
                    'order_id' => $ftcOrder->order_id,
                    'updated_by' => Auth::id(),
                    'action' => 'Uploaded',
                    'file_name' => $file['file_name'],
                    'updated_at' => now(),
                ];
        
                \Log::info("Supporting document saved", ['file' => $filename]);
            }
        
            // Step 8: Bulk insert supporting documents if there are any
            if (!empty($filesToInsert)) {
                SupportingDocs::insert($filesToInsert);  // Bulk insert the files at once
                \Log::info("Bulk insert completed for supporting documents", ['order_id' => $ftcOrder->order_id]);
            }
        
            // Step 9: Bulk insert history records if there are any
            if (!empty($historyToInsert)) {
                OmsAttachmentHistory::insert($historyToInsert);  // Bulk insert history records
                \Log::info("Bulk insert completed for attachment history", ['order_id' => $ftcOrder->order_id]);
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
