<?php

namespace App\Console\Commands;

use App\Models\Mrss;
use App\Jobs\MrssQueryJob;
use Illuminate\Console\Command;

class MrssQueryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mrss:query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'query mrss';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mrsses = json_decode(json_encode(Mrss::where(['auto_import_new' => 1, 'status' => Mrss::STATUS_STARTED])->withCount('entries')->get()), true);
        
        dispatch(new MrssQueryJob($mrsses, 1));
    }
}