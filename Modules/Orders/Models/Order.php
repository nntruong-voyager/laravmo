<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Users\Models\User;

class Order extends Model
{
    /**
     * Use module-specific database connection.
     * Falls back to default connection if not configured.
     */
    protected $connection = 'orders';
    
    protected $fillable = [
        'user_id',
        'product_sku',
        'quantity',
        'total',
        'status',
    ];

    /**
     * Cross-module relationship: Orders reference Users from different DB.
     * In microservices, this would be a remote reference (user_id only).
     */
    public function user(): BelongsTo
    {
        // For now, we allow cross-DB relationships.
        // In microservices, this would be replaced with a service call.
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

