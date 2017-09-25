<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverArtRating extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'airshr_coverart_ratings';

    protected $guarded = array();

}
