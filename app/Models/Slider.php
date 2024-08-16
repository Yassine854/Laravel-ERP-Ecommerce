<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'title',
        'description',
        'user_id',
    ];
    public function user()
{
    return $this->belongsTo(User::class);
}
}
