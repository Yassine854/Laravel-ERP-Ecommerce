<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;
    protected $table = 'product_attribute_value';

    // Define which fields can be mass-assigned
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value_id',
        'stock',
        'unit_price'
    ];


    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship with Attribute
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    // Relationship with Value
    public function value()
    {
        return $this->belongsTo(Value::class);
    }
}
