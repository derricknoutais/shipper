<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Jobs\InsertPulledProductsToDatabase;
use App\Jobs\PullProductsFromPullDBIntoRedis;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->call(function () {
                PullProductsFromPullDBIntoRedis::dispatch();
                InsertPulledProductsToDatabase::dispatch();
                InsertDistinctHandles::dispatch();
                PullAndInsertArticlesFromFidbak::dispatch();
            })
            ->everyFiveMinutes();

        $schedule
            ->call(function () {
                Log::info("I'm running");
            })
            ->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
