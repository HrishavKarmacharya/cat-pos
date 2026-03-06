<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_system',
    ];

    /**
     * Get the ID of the system guest customer record.
     */
    public static function getGuestId()
    {
        return self::where('is_system', true)->first()?->id;
    }

    /**
     * Get the customer's phone number formatted.
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
