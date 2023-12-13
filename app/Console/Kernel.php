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
            })
            ->everyTenMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
