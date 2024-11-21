<?php

namespace App\Http\Traits;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

trait FastTaxAPI
{
    
    public function getFtcToken() {
        $response = Http::timeout(60)->withOptions([
            'verify' => false,
        ])
        ->post(env("FASTTAX_ENDPOINT", "https://portal.fasttaxcert.com/api"). '/ftc/Login.php', [
            'Username' => env("FASTTAX_USERNAME", "lenderlynx"),
            'Password' => env("FASTTAX_PASSWORD", 'lynx@7$Lend!'),
        ]);


        $response = json_decode($response->body(), true);
        if($response['Status'] == "Success" && !empty($response['Token'])){
            Session::put('ftc_token', $response['Token']);
        }
    }

    public function getFtcData($endpoint, $data) {
        $data['Token'] = Session::get('ftc_token');
        $response = Http::timeout(60)->withoutVerifying()
                        ->withOptions(["verify"=>false])
                        ->accept('application/json')
                        ->withBody(json_encode($data), "application/json")
                        ->post(env("FASTTAX_ENDPOINT", "https://portal.fasttaxcert.com/api/"). $endpoint);

        if($response['Status'] == "Failed" && !empty($response['Error']) && (Str::contains(strtolower($response['Error']), ['invalid token', 'token expired']))) {
            $this->getFtcToken();
            $data['Token'] = Session::get('ftc_token');
            $response = Http::timeout(60)->withoutVerifying()
                            ->withOptions(["verify"=>false])
                            ->accept('application/json')
                            ->withBody(json_encode($data), "application/json")
                            ->post(env("FASTTAX_ENDPOINT", "https://portal.fasttaxcert.com/api/"). $endpoint);

            return json_decode($response->body(), true);
        } else {
            return $response;
        }
    }
}
