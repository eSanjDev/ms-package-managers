<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\Manager\Http\Request\ManagerCreateRequest;
use Esanj\Manager\Http\Request\ManagerUpdateRequest;
use Esanj\Manager\Http\Resources\ManagerResource;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerApiController extends BaseController
{
    public function __construct(protected ManagerService $managerService)
    {
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

    public function show(Manager $manager): JsonResponse
    {
        return response()->json([
            'data' => new ManagerResource($manager),
        ]);
    }

    public function store(ManagerCreateRequest $request): JsonResponse
    {
        $requestData = $request->only(['esanj_id', 'role', 'is_active']);
        $requestData['token'] = $request->input('token') ?? $this->managerService->generateToken(config('manager.token_length'));

        $manager = $this->managerService->createManager($requestData);

        $manager->permissions()->sync($request->input('permissions', []));

        return response()->json([
            'data' => new ManagerResource($manager),
            'message' => trans('manager::manager.success.stored')
        ], 201);
    }

    public function update(Manager $manager, ManagerUpdateRequest $request): JsonResponse
    {
        $updateData = $request->only(['role', 'is_active', 'name']);

        if ($request->filled('token')) {
            $updateData['token'] = $request->input('token');
        }

        $manager = $this->managerService->updateManager($manager->id, $updateData);

        $manager->permissions()->sync($request->input('permissions', []));

        return response()->json([
            'data' => new ManagerResource($manager),
            'message' => trans('manager::manager.success.updated')
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
