<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class User extends Resource
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
            'status' => 'success',
            'data' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'token' => $this->token,
                'expire_at' => $this->expire_at,
            ],
        ];
    }
}
