<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Setting extends Resource
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
            'download_start' => $this->start,
            'download_stop' => $this->stop,
            'download_path' => $this->download_path,
        ];
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
