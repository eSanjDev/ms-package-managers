<?php

namespace Esanj\Manager\Http\Controllers;

use App\Http\Controllers\Controller;
use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;
use Esanj\Manager\Http\Resources\ManagerResource;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerApiController extends BaseController
{
    public function __construct(protected ManagerService $managerService)
    {
        $this->middleware(CheckManagerPermissionMiddleware::class . ':managers.delete')
            ->only(['destroy', 'restore']);

        $this->middleware(CheckManagerPermissionMiddleware::class . ':managers.list')
            ->only(['index']);

        $this->middleware(CheckManagerPermissionMiddleware::class . ':managers.create')
            ->only(['regenerate']);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int)$request->get('per_page', 15), 50);

        $query = Manager::query();

        if ($request->boolean('only_trash')) {
            $query->onlyTrashed();
        }

        $managers = $query->paginate($perPage);

        return response()->json([
            'data' => ManagerResource::collection($managers),
            'meta' => [
                'total' => $managers->total(),
                'current_page' => $managers->currentPage(),
                'per_page' => $managers->perPage(),
                'last_page' => $managers->lastPage(),
            ]
        ]);
    }

    public function destroy(Manager $manager): JsonResponse
    {
        $this->managerService->delete($manager->id);

        return response()->json([
            'message' => 'Manager deleted successfully.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $manager = $this->managerService->restoreManager($id);

        if (!$manager) {
            return response()->json([
                'message' => 'Manager not found or already restored.',
            ], 404);
        }

        return response()->json([
            'message' => 'Manager restored successfully.',
            'data' => new ManagerResource($manager),
        ]);
    }

    public function regenerate(): JsonResponse
    {
        $tokenLength = config('manager.token_length', 32);
        $token = $this->managerService->generateToken($tokenLength);

        return response()->json([
            'data' => [
                'token' => $token,
            ],
        ]);
    }
}
