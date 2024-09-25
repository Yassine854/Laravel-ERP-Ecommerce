<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Parametre extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'phone',
        'nature_id',
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

    public function nature(): BelongsTo
    {
        return $this->belongsTo(Nature::class,'nature_id');
    }
}
