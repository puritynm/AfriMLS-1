<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class property extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'properties';

    public function county(){
        return $this->belongsTo('App\Models\county');
    }

    public function category(){
        return $this->belongsTo('App\Models\category');
    }
    public function option(){
        return $this->belongsTo('App\Models\option');
    }

    public function location(){
        return $this->belongsTo('App\Models\location');
    }

    public function garagefeatures(){
        return $this->hasMany('App\Models\garage_feauture');
    }

    public function garagecarports(){
        return $this->hasMany('App\Models\garage_carport');
    }







}
