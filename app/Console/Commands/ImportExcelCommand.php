<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Auth;
use App\Models\CountyInstructions;
use App\Models\State;
use App\Models\County;
use App\Models\City;
use App\Models\stlprocess;
use App\Models\Lob;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CountyImport;
use Illuminate\Support\Facades\File;

class ImportExcelCommand extends Command
{
    protected $signature = 'import:excel {file}';
    protected $description = 'Import data from an Excel file and store it in JSON format';

    public function handle()
    {
        $file = $this->argument('file');

        $import = new CountyImport;
        Excel::import($import, $file);

        foreach ($import->data as $entry) {

           $cityId = null;

            $stateId = State::where('short_code', trim($entry['state']))->value('id');
            if (!$stateId) {
                continue;
            }

            $countyId = County::where('stateId',$stateId)->where('county_name', trim($entry['county']))->value('id');
            if (!$countyId) {
               continue;
            }

            if(!empty(trim($entry['municipality']))){
            $cityId = City::where('county_id',$countyId)->where('city', trim($entry['municipality']))->value('id');
            if (!$cityId) {
                $cityId = City::create([
                    'county_id' =>$countyId,
                    'city' => trim($entry['municipality']),
                ])->id;
            }
        }
            $lobId = Lob::where('name', trim($entry['lob']))->value('id');
            $processId = stlprocess::where('name', trim($entry['process']))->value('id');


            CountyInstructions::create([
                'state_id' => $stateId,
                'county_id' => $countyId,
                'city_id' => $cityId,
                'process_id' => $processId,
                'lob_id' => $lobId,
                'json' => json_encode($entry['json']),
                'last_updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);



        }

        $data = $import->data->toJson(JSON_PRETTY_PRINT);
        $jsonFilePath = storage_path('app/public/template.json');
        File::put($jsonFilePath, $data);

        $this->info('Import completed and data stored in JSON format.');

        return 0;
    }
}
