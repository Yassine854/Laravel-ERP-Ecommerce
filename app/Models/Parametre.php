<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Parametre extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'key_word',
        'temps_travail',
        'email',
        'url_fb',
        'url_insta',
        'url_youtube',
        'url_tiktok',
        'url_twiter',
        'mode_payement',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
