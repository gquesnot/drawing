<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drawing_lien extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $guarded = ['id'];
    public function drawing(){
        return $this->belongsTo(Drawing::class, 'drawing_id');
    }

}
