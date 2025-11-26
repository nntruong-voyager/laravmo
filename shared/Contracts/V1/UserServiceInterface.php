<?php

namespace Shared\Contracts\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Users\Models\User;
use Shared\DTOs\V1\UserDTO;

interface UserServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(UserDTO $data): User;

    public function find(int $id): User;
}

