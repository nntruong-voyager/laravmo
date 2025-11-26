<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Orders\Models\Order;

class Payment extends Model
{
    /**
     * Use module-specific database connection.
     * Falls back to default connection if not configured.
     */
    protected $connection = 'payments';
    
    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    /**
     * Cross-module relationship: Payments reference Orders from different DB.
     * In microservices, this would be a remote reference (order_id only).
     */
    public function order(): BelongsTo
    {
        // For now, we allow cross-DB relationships.
        // In microservices, this would be replaced with a service call.
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}

