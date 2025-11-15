<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\Manager\Http\Request\ManagerCreateRequest;
use Esanj\Manager\Http\Request\ManagerMetaRequest;
use Esanj\Manager\Http\Request\ManagerUpdateRequest;
use Esanj\Manager\Http\Resources\ManagerActivityResource;
use Esanj\Manager\Http\Resources\ManagerResource;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Models\ManagerActivity;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManagerApiController extends BaseController
{
    public function __construct(protected ManagerService $managerService)
    {
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.list'))->only(['index', 'show']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.store'))->only(['store']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.update'))->only(['update']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.delete'))->only(['destroy']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.delete'))->only(['restore']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.meta'))->only(['getMeta', 'setMeta']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.activity'))->only(['activities', 'getLog']);
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
        $requestData = $request->only(['esanj_id', 'role', 'is_active', 'uses_token']);
        $requestData['token'] = $request->input('token') ?? $this->managerService->generateToken();

        $manager = $this->managerService->createManager($requestData);

        $manager->permissions()->sync($request->input('permissions', []));

        return response()->json([
            'data' => new ManagerResource($manager),
            'message' => trans('manager::manager.success.stored')
        ], 201);
    }

    public function update(Manager $manager, ManagerUpdateRequest $request): JsonResponse
    {
        $updateData = $request->only(['role', 'is_active', 'name', 'uses_token']);

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

    public function getMeta(Manager $manager, string $key): JsonResponse
    {
        $meta = $manager->getMeta($key);

        if (!$meta) {
            return response()->json([
                'message' => __('manager::manager.errors.meta_not_found'),
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'value' => $meta->value,
                'created_at' => $meta->created_at->toDateTimeString(),
                'updated_at' => $meta->updated_at->toDateTimeString(),
            ]
        ]);
    }

    public function setMeta(Manager $manager, ManagerMetaRequest $request): JsonResponse
    {
        $manager->setMeta(
            key: $request->input('key'),
            value: $request->input('value')
        );

        return response()->json([
            'status' => true
        ]);
    }

    public function activities(Manager $manager, Request $request): JsonResponse
    {
        $activities = $this->managerService->getActivitiesWithPaginate($manager);

        return response()->json([
            'data' => ManagerActivityResource::collection($activities),
            'meta' => [
                'total' => $activities->total(),
                'current_page' => $activities->currentPage(),
                'per_page' => $activities->perPage(),
                'last_page' => $activities->lastPage(),
            ]
        ]);
    }

    public function getLog(Manager $manager, ManagerActivity $activity): JsonResponse
    {
        return response()->json([
            'data' => new ManagerActivityResource($activity)
        ]);
    }
}
