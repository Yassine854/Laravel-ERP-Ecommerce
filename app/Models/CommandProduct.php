<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class CommandProduct extends Model
{
    protected $table = 'command_product';
    protected $fillable = ['commande_id', 'product_id','attribute_id', 'value_id','quantity', 'price'];

    /**
     * Get the command associated with the pivot.
     */
    public function command()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributes()
    {
        return $this->hasMany(CommandProductAttribute::class, 'commande_product_id');
    }

}
