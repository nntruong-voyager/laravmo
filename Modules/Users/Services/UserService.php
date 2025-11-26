<?php

namespace Modules\Users\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\Users\Models\User;
use Shared\Contracts\V1\UserServiceInterface;
use Shared\DTOs\V1\UserDTO;
use Shared\Events\UserCreated;

class UserService implements UserServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()->latest()->paginate($perPage);
    }

    public function find(int $id): User
    {
        return User::query()->findOrFail($id);
    }

    public function create(UserDTO $data): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password ?? 'password'),
        ]);

        event(new UserCreated(UserDTO::fromModel($user)));

        return $user;
    }
}
