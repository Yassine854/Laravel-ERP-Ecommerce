<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'email',
        'role',
        'blocked',
        'tel',
        'city',
        'address',
        'zip',
        'password',
        'subdomain',
        'pack_id',
        'offre_id',
        'parametre_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];



    public function sliders()
{
    return $this->hasMany(Slider::class);
}

public function parametre()
{
    return $this->belongsTo(User::class);
}

public function pack()
{
    return $this->belongsTo(User::class);
}

public function offre()
{
    return $this->belongsTo(User::class);
}
}