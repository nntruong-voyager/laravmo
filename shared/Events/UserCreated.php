<?php

namespace Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shared\DTOs\V1\UserDTO;

class UserCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly UserDTO $user)
    {
    }

    public function topic(): string
    {
        return config('eventbus.topics.user_created');
    }

    public function payload(): array
    {
        return $this->user->withoutSensitive()->toArray();
    }
}
