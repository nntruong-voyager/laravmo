<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shared\Contracts\V1\UserServiceInterface;
use Shared\DTOs\V1\UserDTO;

class UserController extends Controller
{
    public function __construct(private readonly UserServiceInterface $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = $this->service->create(UserDTO::fromArray($payload));

        return response()->json($user, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->find($id));
    }
}
