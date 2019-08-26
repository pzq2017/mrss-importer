<?php

namespace App\Console\Commands;

use App\Models\Mrss;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use App\Models\Entry;
use Illuminate\Console\Command;

class VideoDownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'download video';
    
    const LOG_TAG = '[download video]: ';
    const DOWNLOADING_NUMBER = 5;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$this->checkCanDownloadByDate()) {
            return false;
        }

        if (Entry::where('status', Entry::STATUS_DOWNLOADING)->count() >= self::DOWNLOADING_NUMBER) {
            Log::error(self::LOG_TAG.'exceed the maximum download of video');
            return;
        }

        $mrss = Mrss::whereHas('entries', function ($query) {
                $query->where('status', Entry::STATUS_PENDING);
            })
            ->with(['entries' => function ($query) {
                return $query->where('status', Entry::STATUS_PENDING)->limit(1);
            }])
            ->where('status', Mrss::STATUS_STARTED)
            ->inRandomOrder()
            ->first();

        if (is_null($mrss)) {
            return;
        }
        Log::info(self::LOG_TAG.'try mrss: '.$mrss->title);

        $entry = $mrss->entries[0];
        if (empty($entry->download_url)) {
            $entry->status = Entry::STATUS_DOWNLOAD_FAILED;
            Log::error(self::LOG_TAG.'the video '.$entry->id.' without download url');
        } else {
            $entry->status = Entry::STATUS_DOWNLOADING;
        }
        $entry->save();

        if (!empty($entry->download_url)) {
            $destinationPath = $this->getTargetPath($mrss, $entry);
            Log::info(self::LOG_TAG.'Downloading '.$entry->title.' to '.$destinationPath);
            if (!is_null($destinationPath)) {
                $artisan = BASE_PATH('artisan');
                $shell = '/bin/bash ' . base_path() . '/app/Console/Commands/download.sh "'.$entry->download_url.'" "'.$destinationPath.'" '.$artisan . ' '.$entry->id.' > /dev/null 2>&1 &';
                shell_exec($shell);
            }
        }
    }

    private function getTargetPath($mrss, $entry)
    {
        $download_path = Setting::first()->value('download_path');
        if (is_null($download_path)) {
            Log::error(self::LOG_TAG.'the video download path not set');
            return null;
        }

        $dirName = str_replace(' ', '_', $mrss->title);
        $target_dir = $download_path.'/'.$dirName;
        if (!is_dir($target_dir)) {
            if (mkdir($target_dir, 0777, true) == false) {
                Log::error(self::LOG_TAG.'the video target dir '.$target_dir.' create failed');
                return null;
            }
        }

        $path = pathinfo($entry->download_url);
        $extension = $path['extension'];
        if (strpos($extension, '?')) {
            $extension_arr = explode('?', $extension);
            $extension = $extension_arr[0];
        }
        $filename = str_replace(' ', '_', $entry->title).'_'.$entry->guid;
        return $download_path.'/'.$dirName.'/'.$filename.'.'.$extension;
    }

    private function checkCanDownloadByDate()
    {
        $today_date = date('Y-m-d');
        $setting = Setting::find(1);
        $start_at = strtotime($today_date.' '.$setting['start']);
        $stop_at = strtotime($today_date.' '.$setting['stop']);
        if ($start_at > $stop_at) {
            $stop_at += 24 * 3600;
        }
        $current_time = time();
        if ($current_time >= $start_at && $current_time <= $stop_at) {
            return true;
        }
        return false;
    }
}