<?php

namespace App\Jobs;

use Log;
use Exception;
use App\Models\Entry;

class MrssQueryJob extends Job
{
    private $mrsses;
    private $mrss_type; // 0-manual 1-automatic 2-for the first time

    const LOG_TAG = '[query mrss]: ';

    public function __construct(array $mrsses, int $mrss_type)
    {
        $this->mrsses = $mrsses;
        $this->mrss_type = $mrss_type;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            if (0 == $this->mrss_type) {
                Log::info(self::LOG_TAG.'import manually not supported currently');
                return;
            }

            foreach ($this->mrsses as $mrss) {

                $initial_import_number = INF;
                if (2 == $this->mrss_type && $mrss['initial_import_number'] > -1) {
                    $initial_import_number = $mrss['initial_import_number'];
                }

                $mrss_xml = simplexml_load_file($mrss['url']);
                $imported_number = 0;
                foreach ($mrss_xml->channel->item as $item) {
                    if (Entry::where(['mrss_id' => $mrss['id'], 'guid' => $item->guid])->count()) {
                        continue;
                    }
                    if ($imported_number >= $initial_import_number) {
                        break;
                    }
                    $item_children = $item->children('media', true);
                    $content_attributes = $item_children->content->attributes();
                    $thumbnail_attributes = $item_children->thumbnail->attributes();
                    $item_published_at = isset($item->pubDate) ? date('Y-m-d H:i:s', strtotime($item->pubDate)) : NULL;
                    if (1 == $this->mrss_type) {
                        if ($mrss['created_at'] >= $item_published_at && !is_null($item_published_at)) {
                            continue;
                        }
                    }
                    $entry = new Entry;
                    $entry->mrss_id = $mrss['id'];
                    $entry->guid = $item->guid;
                    $entry->title = $item->title;
                    $entry->description = $item->description;
                    $entry->media_type = $content_attributes->type;
                    $entry->duration = $content_attributes->duration;
                    $entry->width = $content_attributes->width;
                    $entry->height =  $content_attributes->height;
                    $entry->lang = $content_attributes->lang;
                    $entry->category = $item_children->category;
                    $entry->keywords = $item_children->keywords;
                    $entry->download_url = $content_attributes->url;
                    $entry->thumbnail_url = $thumbnail_attributes->url;
                    $entry->status = Entry::STATUS_PENDING;
                    $entry->published_at = $item_published_at;
                    if ($entry->save()) {
                        Log::info(self::LOG_TAG.'saved one entry which mrss_id is ['.$mrss['id'].'] and guid is ['.$item->guid.'] successfully');
                        $imported_number++;
                    } else {
                        Log::info(self::LOG_TAG.'saved one entry which mrss_id is ['.$mrss['id'].'] and guid is ['.$item->guid.'] unsuccessfully');
                    }
                }
            }
        } catch (Exception $e) {
            Log::error(self::LOG_TAG.$e->getMessage());
        }
    }
}
