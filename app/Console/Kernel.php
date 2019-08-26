<?php

namespace App\Console;

use App\Console\Commands\MrssQueryCommand;
use App\Console\Commands\VideoDownloadCheckCommand;
use App\Console\Commands\VideoDownloadCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        VideoDownloadCommand::class,
        VideoDownloadCheckCommand::class,
        MrssQueryCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('mrss:query')->everyMinute();
        $schedule->command('video:download')->everyMinute();
    }
}
