<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'image_path',
        'cost_price',
        'price', // Renamed from selling_price
        'stock_quantity',
        'category_id',
        'brand_id',
        'unit_id',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Safely reduce stock for this product.
     * 
     * @param int $quantity Amount to reduce
     * @param string $reason Reason for reduction
     * @param int|null $saleId Optional related sale ID
     * @throws \Exception If stock is insufficient
     */
    public function reduceStock(int $quantity, string $reason, ?int $saleId = null)
    {
        if ($this->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock for product '{$this->name}' (Requested: {$quantity}, Available: {$this->stock_quantity})");
        }

        $this->decrement('stock_quantity', $quantity);

        StockMovement::create([
            'product_id' => $this->id,
            'user_id' => auth()->id() ?? 1, // Fallback for seeds/tests
            'type' => 'out',
            'quantity' => $quantity,
            'date' => now(),
            'sale_id' => $saleId,
            'reason' => $reason,
        ]);
    }

    /**
     * Safely increase stock for this product.
     * 
     * @param int $quantity Amount to increase
     * @param string $reason Reason for increase
     * @param int|null $purchaseId Optional related purchase ID
     */
    public function increaseStock(int $quantity, string $reason, ?int $purchaseId = null)
    {
        $this->increment('stock_quantity', $quantity);

        StockMovement::create([
            'product_id' => $this->id,
            'user_id' => auth()->id() ?? 1,
            'type' => 'in',
            'quantity' => $quantity,
            'date' => now(),
            'sale_id' => null, // keeping schema consistent, though this might need a purchase_id column later or use polymorph. 
                               // For now, based on existing migrations, we might not have purchase_id in stock_movements? 
                               // Let's check `StockMovement` model or migration if possible. 
                               // Looking at previous `StockMovement` content, it has `sale_id`. It does NOT seem to have `purchase_id`.
                               // The `PurchaseController` was putting invoice number in 'reason'. I will follow that pattern.
            'reason' => $reason,
        ]);
    }
}
