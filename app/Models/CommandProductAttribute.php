<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class CommandProductAttribute extends Model
{
    use HasFactory;
    protected $table = 'command_product_attribute';
    protected $fillable = ['commande_product_id','attribute_id', 'value_id'];

    /**
     * Get the command associated with the pivot.
     */
    public function value()
    {
        return $this->belongsTo(Value::class, 'value_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
