<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SummarizeSales implements ShouldQueue
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
        $sales = array_unique( Redis::zrange('sales', 0, -1));

        foreach($sales as $sale){
            if(! Redis::exists('sales_summary.'. Carbon::parse($sale['sale_date'])->format('Y-m-d'))){
                Redis::hmset('sales_summary.'. Carbon::parse($sale['sale_date'])->format('Y-m-d'), [
                    'sale_date' => Carbon::parse($sale['sale_date'])->format('Y-m-d'),
                    'espèce' => 0,
                    '' => 0,
                    'total_discount' => 0,
                    'total_shipping' => 0,
                    'total_cost' => 0,
                    'total_profit' => 0,
                    'total_quantity' => 0,
                ]);
            } else {

            }
            // Redis::hmset('sales_summary.' $sale[''], )
        }
    }
}
