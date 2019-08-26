<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use App\Models\Entry;
use Illuminate\Console\Command;

class VideoDownloadCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:download_check {entryId} {download_status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'download video check';
    
    const LOG_TAG = '[download_check]: ';
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $entryId = intval($this->argument('entryId'));
        $download_status = intval($this->argument('download_status'));
        Log::info(self::LOG_TAG.'download check starting...entryId =>'.$entryId.' download status =>'.$download_status);
        if ($entryId < 1) {
            Log::error(self::LOG_TAG.'invalid arguments.');
        } else {
            $entry = Entry::find($entryId);
            if (!is_null($entry)) {
                if ($download_status == 1) {
                    $entry->status = Entry::STATUS_DOWNLOADED;
                    Log::info(self::LOG_TAG.'video downloaded '.$entryId.' successful.');
                } else {
                    $entry->status = Entry::STATUS_DOWNLOAD_FAILED;
                    Log::error(self::LOG_TAG.'video downloaded '.$entryId.' failed.');
                }
                $entry->save();
            }
        }
    }
}