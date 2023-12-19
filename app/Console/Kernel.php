<?php

namespace App\Console;

use App\Jobs\SortAndInsertHandles;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\InsertPulledProductsToDatabase;
use App\Jobs\PullAndInsertArticlesFromFidbak;
use App\Jobs\PullProductsFromPullDBIntoRedis;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->call(function () {
                // PullProductsFromPullDBIntoRedis::dispatch();
                InsertPulledProductsToDatabase::dispatch();
                // SortAndInsertHandles::dispatch();
                // PullAndInsertArticlesFromFidbak::dispatch();
            })
            ->everyMinute();

        // $schedule
        //     ->call(function () {
        //         Log::info("I'm running");
        //     })
        //     ->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
