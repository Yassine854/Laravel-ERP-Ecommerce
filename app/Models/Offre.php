<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class offre extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'prix',
        'pack_id'
    ];


    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class,'pack_id');
    }
}
