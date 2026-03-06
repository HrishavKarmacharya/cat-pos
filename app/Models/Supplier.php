<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
    ];

    /**
     * Get the supplier's phone number formatted.
     */
    public function getFormattedPhoneAttribute()
    {
        $phone = $this->phone;
        // Assuming a 10-digit number for basic formatting
        if (strlen($phone) === 10 && is_numeric($phone)) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        }

        return $phone;
    }
}
