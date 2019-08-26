<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mrss extends Model
{
    use SoftDeletes;

    protected $table = 'mrss';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'title', 'url', 'initial_import_number', 'auto_import_new', 'status'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    const STATUS_STARTED = 'started';
    const STATUS_STOPED = 'stoped';

    public function entries()
    {
        return $this->hasMany(Entry::class, 'mrss_id');
    }
}
