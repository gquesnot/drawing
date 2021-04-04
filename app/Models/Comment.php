<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }

    public function drawing(){
        return $this->belongsTo('App\Drawing', 'drawing_id');
    }
}
