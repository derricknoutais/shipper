<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class PullProductsFromPullDBIntoRedis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('\/\/\/ - Pulling Products - \/\/\/');
        $response = Http::timeout(6000)
            ->get(env('PULLDB_URL') . '/api/products')
            ->json();
        Redis::del('pulled_products');
        Redis::set('pulled_products', json_encode($response));
        Log::info('/\/\/\ - Done Pulling Products - /\/\/\\');
    }
}
