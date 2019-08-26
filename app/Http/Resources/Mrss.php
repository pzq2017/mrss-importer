<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Mrss extends Resource
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
            'user_id' => intval($this->user_id),
            'title' => $this->title,
            'url' => $this->url,
            'status' => $this->status,
            'initial_import_number' => intval($this->initial_import_number),
            'auto_import_new' => intval($this->auto_import_new),
            'entry_counts' => intval($this->entries_count),
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
