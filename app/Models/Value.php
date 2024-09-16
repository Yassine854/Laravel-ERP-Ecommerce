<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Value extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'attribute_id'
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
