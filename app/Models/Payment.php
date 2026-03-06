<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'gateway',
        'khalti_pidx',
        'khalti_transaction_id',
        'transaction_uuid',
        'order_id',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'transaction_id',
        'raw_response',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the customer associated with the payment via the sale.
     */
    public function getCustomerAttribute()
    {
        return $this->sale ? $this->sale->customer : null;
    }
}
