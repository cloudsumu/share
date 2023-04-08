<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $key = config('constants.currency_exchange_key');
        $request = $client->get("http://api.exchangeratesapi.io/v1/latest?access_key=$key");
        $response = $request->getBody()->getContents();
        $response = json_decode($response, true);
        $rates = $response['rates'];

        $valueINR = $rates['INR'];

        // Rates not evaluating correct values.
        foreach($rates as $key => $value) {
            $rates[$key] = round($value/$valueINR, 3) == 0 ? round($value/$valueINR, 6) : round($value/$valueINR, 2);
        }

        $rates = json_encode($rates);

        DB::table('currencies_updates')->insert([
            'values' => $rates,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
