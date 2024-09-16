<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;
use MongoDB\Laravel\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'brand',
        'stock',
        'image',
        'category_id',
        'user_id'

    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commandes()
    {
        return $this->hasMany(CommandProduct::class, 'product_id');
    }


    public function attributes()
    {
        return $this->hasMany(ProductAttributeValue::class, 'product_id');
    }

}
