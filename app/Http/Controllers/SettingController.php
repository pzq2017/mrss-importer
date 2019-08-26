<?php

namespace App\Http\Controllers;

use App\Http\Resources\Setting as SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function get()
    {
        $setting = Setting::first();
        if ($setting) {
            return new SettingResource($setting);
        } else {
            return $this->responseErrorWithMessage('there is no setting');
        }
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $setting = Setting::first();
        if ($setting) {
            $setting->start = $request->download_start;
            $setting->stop = $request->download_stop;
            $setting->download_path = $request->download_path;
            
            if ($setting->save()) {
                return $this->responseSuccessWithMessage();
            } else {
                return $this->responseErrorWithMessage('update settings unsuccessfully');
            }
        } else {
            if (Setting::create([
                'start' => $request->download_start,
                'stop' => $request->download_stop,
                'download_path' => $request->download_path,
            ])) {
                return $this->responseSuccessWithMessage();
            } else {
                return $this->responseErrorWithMessage('save settings unsuccessfully');
            }
        }
        
    }

    private function validateRequest($request)
    {
        $this->validateWithException($request, [
            'download_start' => 'required|date_format:H:i:s',
            'download_stop' => 'required|date_format:H:i:s',
            'download_path' => 'required',
        ]);
    }
}
