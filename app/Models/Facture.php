<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'admin_id',
        'commande_id',
        'facture_date',
        'facture_tva',
        'total_amount',
        'status',
    ];
    protected static function booted()
    {
        static::creating(function ($commande) {
            $commande->serial_number = static::generateSerialNumber();
        });
    }

    protected static function generateSerialNumber()
    {
        // Find the last serial number
        $lastSerial = static::latest('created_at')->first()->serial_number ?? 'FAC0000';

        // Extract the number from the last serial number
        $lastNumber = intval(substr($lastSerial, 3)); // Adjust to 'CMD1000'

        // Increment the number
        $newNumber = $lastNumber + 1;

        // Generate the new serial number
        return 'FAC' . $newNumber;
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function command()
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }
}
