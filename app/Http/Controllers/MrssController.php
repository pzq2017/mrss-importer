<?php

namespace App\Http\Controllers;

use App\Http\Resources\EntryCollection;
use App\Http\Resources\Entry as EntryResource;
use App\Http\Resources\MrssCollection;
use App\Http\Resources\Mrss as MrssResource;
use App\Models\Entry;
use App\Models\Mrss;
use App\Jobs\MrssQueryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MrssController extends Controller
{
    public function index(Request $request)
    {
        $mrss = Mrss::withCount('entries')
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })->when($request->keyword, function ($query) use ($request) {
                return $query->where('title', 'like', '%'.$request->keyword.'%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return new MrssCollection($mrss);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $mrss = Mrss::create([
            'title' => $request->title,
            'url' => $request->url,
            'user_id' => Auth::user()->id,
            'initial_import_number' => $request->initial_import_number ?? 0,
            'auto_import_new' => $request->auto_import_new ?? 0,
            'status' => Mrss::STATUS_STARTED
        ]);

        dispatch(new MrssQueryJob([$mrss], 2));

        return $this->responseSuccessWithMessage();
    }

    public function update(Request $request, $mrss)
    {
        $mrss = Mrss::findOrFail($mrss);

        $this->validateRequest($request, $mrss->id);

        $mrss->title = $request->title;
        $mrss->url = $request->url;
        $mrss->auto_import_new = $request->auto_import_new ?? 0;
        $mrss->save();

        return $this->responseSuccessWithMessage();
    }

    public function destroy(Request $request, $mrss)
    {
        $mrss = Mrss::findOrFail($mrss);
        $mrss->delete();
        return $this->responseSuccessWithMessage();
    }

    public function info(Request $request, $mrss)
    {
        return new MrssResource(Mrss::findOrFail($mrss));
    }

    public function action(Request $request, $mrss)
    {
        $mrss = Mrss::findOrFail($mrss);

        $this->validateWithException($request, [
            'action' => ['required', Rule::in([Mrss::STATUS_STARTED, Mrss::STATUS_STOPED])]
        ]);

        $mrss->status = $request->action;
        $mrss->save();

        return $this->responseSuccessWithMessage();
    }

    public function entries(Request $request, $mrss)
    {
        $entries = Entry::where('mrss_id', $mrss)->when($request->status, function ($query) use ($request) {
            return $query->where('status', $request->status);
        })->when($request->keyword, function ($query) use ($request) {
            return $query->where('title', 'like', '%'.$request->keyword.'%');
        })->orderBy('created_at', 'DESC')->paginate(10);

        return new EntryCollection($entries);
    }

    public function entry(Request $request, $mrss, $entry)
    {
        return new EntryResource(Entry::with('mrss')->findOrFail($entry));
    }

    private function validateRequest($request, $editId=0)
    {
        $this->validateWithException($request, [
            'title' => 'required',
            'url' => [
                'required',
                'url',
                Rule::unique('mrss')->whereNull('deleted_at')->whereNot('id', $editId),
            ],
            'initial_import_number' => 'int|min:-1',
            'auto_import_new' => ['int', Rule::in([0, 1])],
        ]);
    }
}
