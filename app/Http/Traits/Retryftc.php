<?php

namespace App\Http\Traits;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\SupportingDocs;
use App\Models\OmsAttachmentHistory;
use App\Http\Controllers\OrderFormController;
use Illuminate\Support\Facades\Artisan;


trait Retryftc
{
    /**
     * Process FTC Order.
     *
     * @param int $orderId
     * @return void
     */
    public function processFtcOrder($orderId)
    {
        $ftcOrder = DB::table('ftc_order_data')->where('order_id', $orderId)->first();

        if (!$ftcOrder) {
            Log::error("FTC order not found", ['order_id' => $orderId]);
            return;
        }

        $data = ["OrderId" => $ftcOrder->ftc_order_id];
        $ftcResponse = $this->getFtcData('ftc/GetOrderStatusFTC.php', $data);
        if (empty($ftcResponse)) {
            Log::error("Empty response from FTC API", ['order_id' => $orderId]);
            return;
        }
        if (
            $ftcResponse['result'] === null ||
            (isset($ftcResponse['Status']) &&
            in_array($ftcResponse['Status'], ["In Progress"]))
        ) {
            Log::info("FTC response needs retry", ['response' => $ftcResponse, 'order_id' => $orderId]);
        
            // Trigger the Laravel command with the order ID as a parameter
            Artisan::call('process:ftc-orders', ['orderId' => $orderId]);
        
            return; // Exit the current process to avoid duplication
        } elseif (in_array($ftcResponse['Status'], ["Completed!!!Not supported currently!!!"])) {
            Log::info("Not Supported", ['order_id' => $orderId]);
            return "Not Supported";
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

            Log::info("FTC order data updated", ['order_id' => $orderId]);

            $this->handleTaxes($ftcOrder, $ftcResponse['result']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Database transaction failed", ['error' => $e->getMessage()]);
            return;
        }

        $this->processSupportingFiles($ftcOrder, $ftcResponse['supportfiles']);
    }

    /**
     * Handle tax records.
     *
     * @param object $ftcOrder
     * @param string $ftcResponseResult
     * @return void
     */
    private function handleTaxes($ftcOrder, $ftcResponseResult)
    {
        $existingTaxRecord = DB::table('taxes')->where('order_id', $ftcOrder->order_id)->first();

        if ($existingTaxRecord) {
            DB::table('taxes')->where('order_id', $ftcOrder->order_id)->delete();
        }

        DB::table('taxes')->insert([
            'order_id' => $ftcOrder->order_id,
            'json' => $ftcResponseResult,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        Log::info("Tax record inserted", ['order_id' => $ftcOrder->order_id]);
    }

    /**
     * Process supporting files.
     *
     * @param object $ftcOrder
     * @param string $supportFilesJson
     * @return void
     */
    private function processSupportingFiles($ftcOrder, $supportFilesJson)
    {
        try {
            $supportFiles = json_decode($supportFilesJson, true);
            $filesToInsert = [];
            $historyToInsert = [];

            OmsAttachmentHistory::where('order_id', $ftcOrder->order_id)->delete();

            $existingRecords = SupportingDocs::where('order_id', $ftcOrder->order_id)->get();

            foreach ($existingRecords as $existingRecord) {
                if (Storage::exists($existingRecord->file_path)) {
                    Storage::delete($existingRecord->file_path);
                }

                $existingRecord->delete();
                Log::info("Existing supporting document and file deleted", [
                    'order_id' => $ftcOrder->order_id,
                    'file_name' => $existingRecord->file_name
                ]);
            }

            foreach ($supportFiles['fileslist'] as $file) {
                $decodedData = base64_decode($file['file']);
                $filename = uniqid() . '_' . $file['file_name'];
                $filePath = "taxcert/$ftcOrder->order_id/supporting_docs/$filename";

                Storage::disk('public')->put($filePath, $decodedData);

                $filesToInsert[] = [
                    'order_id' => $ftcOrder->order_id,
                    'file_path' => $filePath,
                    'file_name' => $file['file_name'],
                    'created_at' => now(),
                ];

                $historyToInsert[] = [
                    'order_id' => $ftcOrder->order_id,
                    'updated_by' => auth()->id(),
                    'action' => 'Fetched',
                    'file_name' => $file['file_name'],
                    'updated_at' => now(),
                ];

                Log::info("Supporting document saved", ['file' => $filename]);
            }

            if (!empty($filesToInsert)) {
                SupportingDocs::insert($filesToInsert);
                Log::info("Bulk insert completed for supporting documents", ['order_id' => $ftcOrder->order_id]);
            }

            if (!empty($historyToInsert)) {
                OmsAttachmentHistory::insert($historyToInsert);
                Log::info("Bulk insert completed for attachment history", ['order_id' => $ftcOrder->order_id]);
            }
        } catch (\Exception $e) {
            Log::error("Error while processing documents", ['error' => $e->getMessage()]);
        }
    }
}
