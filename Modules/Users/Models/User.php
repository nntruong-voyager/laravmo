<?php

namespace Modules\Users\Models;

use App\Models\User as BaseUser;

class User extends BaseUser
{
    protected $table = 'users';
    
    /**
     * Use module-specific database connection.
     * Falls back to default connection if not configured.
     */
    protected $connection = 'users';
}

