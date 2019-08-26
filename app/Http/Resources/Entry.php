<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Entry extends Resource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'mrss' => new Mrss($this->mrss),
            'guid' => $this->guid,
            'title' => $this->title,
            'description' => $this->description,
            'media_type' => $this->media_type,
            'duration' => $this->duration,
            'file_size' => $this->file_size,
            'width' => $this->width,
            'height' => $this->height,
            'lang' => $this->lang,
            'category' => $this->category,
            'keywords' => $this->keywords,
            'download_url' => $this->download_url,
            'thumbnail_url' => $this->thumbnail_url,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
