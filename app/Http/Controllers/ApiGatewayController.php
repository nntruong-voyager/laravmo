<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

/**
 * API Gateway Controller
 *
 * Central entry point for all API requests. Routes requests to appropriate
 * module controllers. This allows for:
 * - Request/response transformation
 * - Rate limiting per module
 * - Authentication/authorization
 * - Request logging
 * - Service discovery (when moving to microservices)
 */
class ApiGatewayController extends Controller
{
    /**
     * Route incoming API requests to module controllers.
     *
     * @param Request $request
     * @param string $module The module name (users, orders, payments, inventory)
     * @param string|null $path The remaining path after module name
     * @return JsonResponse
     */
    public function route(Request $request, string $module, ?string $path = null): JsonResponse
    {
        // Validate module exists and is enabled
        $moduleInstance = Module::find($module);
        if (!$moduleInstance || !$moduleInstance->isEnabled()) {
            return response()->json([
                'error' => 'Module not found or disabled',
                'module' => $module,
            ], 404);
        }

        // Build the target route path
        $targetPath = $path ? "/{$path}" : '';
        $fullPath = "/api/{$module}{$targetPath}";

        // Find matching route
        $route = Route::getRoutes()->match(
            $request->create($fullPath, $request->method(), $request->all())
        );

        if (!$route) {
            return response()->json([
                'error' => 'Route not found',
                'path' => $fullPath,
            ], 404);
        }

        // Execute the route and return response
        try {
            $response = $route->run($request);
            
            // If response is already a JsonResponse, return it
            if ($response instanceof JsonResponse) {
                return $response;
            }

            // Otherwise, convert to JSON
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Health check endpoint for gateway.
     */
    public function health(): JsonResponse
    {
        $modules = collect(Module::allEnabled())
            ->map(fn ($module) => [
                'name' => $module->getName(),
                'enabled' => $module->isEnabled(),
            ]);

        return response()->json([
            'status' => 'healthy',
            'modules' => $modules,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

