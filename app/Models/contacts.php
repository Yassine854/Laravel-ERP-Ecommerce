<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class contacts extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'mail',
        'mobile',
        'message'
    ];


}
