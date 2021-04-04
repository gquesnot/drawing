<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drawing extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function data(){
        return $this->hasMany(Drawing_lien::class,"drawing_id");
    }
    public function user(){
        return $this->belongsTo("App\User", "user_id");
    }

    public function comments(){
        return $this->hasMany("App\Comment", "drawing_id");
    }
}
