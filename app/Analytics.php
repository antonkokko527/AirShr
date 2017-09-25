<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Analytics extends Model
{
    use SoftDeletes;

    const LISTENER_ENGAGEMENT = 1;

    protected $fillable = array('type', 'data', 'start_time', 'end_time');

    protected $table = 'airshr_analytics';
}
