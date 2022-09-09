<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;


class SyncSales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 1200;
    public $tries = 1;
    protected $date_from;

    public function __construct($date_from)
    {
        $this->date_from = $date_from;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();
        $headers = [
            "Authorization" => "Bearer " . env('VEND_TOKEN'),
            "Accept"        => "application/json"
        ];

        Log::info("Getting sales after " );

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/search?type=sales&date_from=' . $this->date_from,
            ['headers' => $headers]
        );
        $data = json_decode((string) $response->getBody(), true);
        foreach($data['data'] as $sale)
        {
            Redis::zadd('sales', $sale['invoice_number'], json_encode($sale));
        }
            // $after = $data['version']['max'];
            // Redis::set('after', $after);
        Log::info("Got " . count($data['data']) . " sales");
    }
}
