<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type', // in, out, adjustment
        'quantity',
        'date',
        'sale_id',
        'reason', // Renamed from note
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // RELATIONSHIP
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Create a new stock movement record.
     *
     * @throws ValidationException
     */
    public static function createStockMovement(array $data): StockMovement
    {
        $validator = Validator::make($data, [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                'lte:2147483647', // Max integer value for MySQL INT
            ],
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                'lte:2147483647', // Max integer value for MySQL INT
            ],
            'type' => [
                'required',
                'string',
                'in:in,out,adjustment', // Updated enum values
            ],
            'quantity' => [
                'required',
                'integer',
                'gt:0',
                'lte:2147483647', // Max integer value for MySQL INT
            ],
            'date' => [
                'required',
                'date',
            ],
            'sale_id' => [
                'nullable',
                'integer',
                'exists:sales,id',
                'lte:2147483647', // Max integer value for MySQL INT
            ],
            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return self::create($validator->validated());
    }
}
