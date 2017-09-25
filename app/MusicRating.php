<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MusicRating extends Model
{
    use SoftDeletes;

    protected $fillable = array('artist', 'title', 'data', 'watch', 'station_id', 'last_updated');
    
    protected $table = 'airshr_music_ratings';
    
    public function watch() {
        $this->watch = 1;
        $this->save();
    }
    
}
