<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Pack extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'prix',
    ];
}
