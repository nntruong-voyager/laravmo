<?php

namespace Shared\DTOs\V1;

use Illuminate\Contracts\Support\Arrayable;
use Modules\Users\Models\User;

class UserDTO implements Arrayable
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'],
            $data['email'],
            $data['password'] ?? null,
        );
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
        );
    }

    public function withoutSensitive(): self
    {
        return new self($this->id, $this->name, $this->email);
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ], static fn ($value) => $value !== null);
    }
}

