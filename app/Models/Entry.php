<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use SoftDeletes;

    protected $table = 'entries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['mrss_id', 'guid', 'title', 'description', 'media_type', 'duration', 'file_size', 'width', 'height', 'lang', 'category', 'keywords', 'download_url', '
    thumbnail_url', 'published_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    const STATUS_PENDING = 'pending';
    const STATUS_DOWNLOADING = 'downloading';
    const STATUS_DOWNLOADED = 'downloaded';
    const STATUS_DOWNLOAD_FAILED = 'downloaded_failed';

    public function mrss()
    {
        return $this->belongsTo(Mrss::class, 'mrss_id');
    }
}
