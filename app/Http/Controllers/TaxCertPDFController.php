<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Tax;
use mikehaertl\pdftk\Pdf as pdfTk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TaxCertPDFController extends Controller
{
    public function fillPDF($orderId,$DataJson)
    {
        $orderData = Tax::where('order_id', $orderId)->first();

        if (!$orderData) {
            return response()->json(['error' => 'Order data not found'], 404);
        }

        $templatePdf = storage_path('app/taxcert_template/tax_worksheet_2024.pdf');

        if (!file_exists($templatePdf)) {
            Log::error("Template PDF not found at: {$templatePdf}");
            return response()->json(['error' => 'Template PDF file does not exist'], 500);
        }

        $directoryPath = storage_path("app/public/taxcert/{$orderId}");
        $outputFilePath = "{$directoryPath}/Certificate.pdf";

        if (!is_dir($directoryPath)) {
            try {
                Storage::makeDirectory("public/taxcert/{$orderId}");
                chmod($directoryPath, 0777);
            } catch (\Exception $e) {
                Log::error("Error creating directory: {$e->getMessage()}");
                return response()->json(['error' => 'Failed to create directory'], 500);
            }
        }
        // dd($responseDataJson);
        $data = json_decode($DataJson, true);
        // $inputData = json_decode($orderData->input, true);
        // $fileno = $inputData['InputInfo']['file_number'] ?? '';

        $pdfcount = 0;
        $pdfFiles = [];
        foreach ($data['tax_cert'] as $taxcertData) {
            $pdf = new pdfTk($templatePdf);
            $pdfFields = [];
            $i = 0;
            foreach ($taxcertData['payment_info'] as $paymentData) {
                $pdfFields["taxyear.{$i}"] = !empty($paymentData['tax_year']) ? $paymentData['tax_year'] : '';
                $pdfFields["taxtype.{$i}"] = !empty($paymentData['tax_type']) ? $paymentData['tax_type'] : '';
                $pdfFields["taxperiod.{$i}"] = !empty($paymentData['tax_period']) ? $paymentData['tax_period'] : '';
                $pdfFields["amount.{$i}"] = !empty($paymentData['tax_amount']) ? '$' . number_format($paymentData['tax_amount'], 2) : '0.00';
                $pdfFields["status.{$i}"] = !empty($paymentData['status']) ? $paymentData['status'] : '';
                $pdfFields["duedate.{$i}"] = !empty($paymentData['due_date']) ? date('m/d/Y', strtotime($paymentData['due_date'])) : '';
                $pdfFields["delinquentdate.{$i}"] = !empty($paymentData['delinquent_date']) ? date('m/d/Y', strtotime($paymentData['delinquent_date'])) : '';
                $pdfFields["paiddate.{$i}"] = !empty($paymentData['paid_date']) ? date('m/d/Y', strtotime($paymentData['paid_date'])) : '';
                $i++;
            }

            $tax_cert_fieldlist = $taxcertData['fieldlist'];
            // $pdfFields["order_fileno"] = $fileno;
            $pdfFields["tax_parcel_id"] = !empty($tax_cert_fieldlist['tax_parcel_id']) ? $tax_cert_fieldlist['tax_parcel_id'] : '';
            $pdfFields["account_number"] = !empty($tax_cert_fieldlist['account_number']) ? $tax_cert_fieldlist['account_number'] : '';
            $pdfFields["township"] = !empty($tax_cert_fieldlist['township']) ? $tax_cert_fieldlist['township'] : '';
            $pdfFields["assessed_land_value"] = !empty($tax_cert_fieldlist['assessed_land_value']) ? number_format($tax_cert_fieldlist['assessed_land_value'], 2) : '0.00';
            $pdfFields["assessed_improvement_value"] = !empty($tax_cert_fieldlist['assessed_improvement_value']) ? number_format($tax_cert_fieldlist['assessed_improvement_value'], 2) : '0.00';
            $pdfFields["assessed_total_value"] = !empty($tax_cert_fieldlist['assessed_total_value']) ? number_format($tax_cert_fieldlist['assessed_total_value'], 2) : '0.00';
            $pdfFields["is_tax_sale"] = !empty($tax_cert_fieldlist['is_tax_sale']) ? $tax_cert_fieldlist['is_tax_sale'] : 'No';
            $pdfFields["is_prior_tax_paid"] = !empty($tax_cert_fieldlist['is_prior_tax_paid']) ? $tax_cert_fieldlist['is_prior_tax_paid'] : 'No';
            $pdfFields["is_other_exemption"] = !empty($tax_cert_fieldlist['is_other_exemption']) ? $tax_cert_fieldlist['is_other_exemption'] : 'Off';
            $pdfFields["is_homestead_exemption"] = !empty($tax_cert_fieldlist['is_homestead_exemption']) ? $tax_cert_fieldlist['is_homestead_exemption'] : 'Off';
            $pdfFields["exemption_percentage"] = !empty($tax_cert_fieldlist['exemption_percentage']) ? $tax_cert_fieldlist['exemption_percentage'] : '0.00';
            $pdfFields["exemption_amount"] = !empty($tax_cert_fieldlist['exemption_amount']) ? number_format($tax_cert_fieldlist['exemption_amount'], 2) : '0.00';
            $pdfFields["notes"] = !empty($tax_cert_fieldlist['notes']) ? $tax_cert_fieldlist['notes'] : '';
        }

        $tempPdfPath = "{$directoryPath}/Tax Certificate_{$pdfFields['tax_parcel_id']}.pdf";

        $pdf = new pdfTk($templatePdf);
        $result = $pdf->fillForm($pdfFields)->flatten()->saveAs($tempPdfPath);

        if (!$result) {
            Log::error("PDFtk Error: " . $pdf->getError());
            return response()->json(['error' => 'Failed to generate PDF'], 500);
        }

        // Merge PDFs (if needed)
        // Example: $gsCommand = ...

        try {
            chmod($tempPdfPath, 0777);
        } catch (\Exception $e) {
            Log::error("Error changing file permissions: {$e->getMessage()}");
        }

        return response()->json(['success' => 'PDF generated successfully', 'path' => $tempPdfPath]);
    }
}
