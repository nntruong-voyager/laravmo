<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * Use module-specific database connection.
     * Falls back to default connection if not configured.
     */
    protected $connection = 'inventory';
    
    protected $fillable = [
        'sku',
        'name',
        'price',
        'stock',
    ];
}

