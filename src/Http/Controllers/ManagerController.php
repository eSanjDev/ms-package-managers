<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Http\Request\ManagerCreateRequest;
use Esanj\Manager\Http\Request\ManagerUpdateRequest;
use Esanj\Manager\Http\Resources\ManagerActivityResource;
use Esanj\Manager\Http\Resources\ManagerResource;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Models\ManagerActivity;
use Esanj\Manager\Models\Permission;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManagerController extends BaseController
{
    public function __construct(protected ManagerService $managerService)
    {
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.list'))->only(['index']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.store'))->only(['create', 'store']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.update'))->only(['edit', 'update']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.delete'))->only(['destroy']);
        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.restore'))->only(['restore']);

        $this->middleware("manager.permission:" . config('esanj.manager.access_provider.activity'))->only(['activities'], 'getLog');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $managers = $this->managerService->getManagersWithPaginate();

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

        return view('manager::panel.index');
    }

    public function create(): View
    {
        $isAdmin = $this->currentUserIsAdmin();

        $roles = $this->getAvailableRoles($isAdmin);
        $permissions = $this->getGroupedPermissions();
        $token = $this->managerService->generateToken();

        return view('manager::panel.create', compact('roles', 'isAdmin', 'permissions', 'token'));
    }

    public function store(ManagerCreateRequest $request): RedirectResponse
    {
        $isAdmin = $this->currentUserIsAdmin();

        if (!$isAdmin && $request->input('role') === ManagerRoleEnum::Admin) {
            return back()->withErrors([
                'role' => trans('manager::manager.errors.role_not_allowed')
            ]);
        }

        $requestData = $request->only(['name', 'esanj_id', 'role', 'is_active', 'uses_token']);
        $requestData['token'] = $request->input('token') ?? $this->managerService->generateToken();

        $manager = $this->managerService->createManager($requestData);

        $manager->permissions()->sync($request->input('permissions', []));

        return redirect()->route('managers.edit', $manager->id)
            ->with('success', trans('manager::manager.success.stored'));
    }

    public function edit(Manager $manager): View
    {
        $isAdmin = $this->currentUserIsAdmin();

        $roles = $this->getAvailableRoles($isAdmin);
        $permissions = $this->getGroupedPermissions();
        $managerPermissions = $manager->permissions->pluck('id')->toArray();

        return view('manager::panel.edit', compact(
            'manager', 'isAdmin', 'roles', 'permissions', 'managerPermissions'
        ));
    }

    public function update(ManagerUpdateRequest $request, Manager $manager): RedirectResponse
    {
        $updateData = $request->only(['role', 'is_active', 'name', 'uses_token']);

        if ($request->filled('token')) {
            $updateData['token'] = $request->input('token');
        }

        $this->managerService->updateManager($manager->id, $updateData);

        $manager->permissions()->sync($request->input('permissions', []));

        return redirect()->route('managers.edit', $manager->id)
            ->with('success', trans('manager::manager.success.updated'));
    }

    public function destroy(Request $request, Manager $manager)
    {
        if ($request->ajax()) {
            $this->managerService->delete($manager->id);

            return response()->json([
                'message' => 'Manager deleted successfully.',
            ]);
        }

        return redirect()->route('managers.index');
    }

    public function restore(Request $request, int $id)
    {
        if ($request->ajax()) {
            $manager = $this->managerService->restoreManager($id);

            return response()->json([
                'message' => 'Manager restored successfully.',
                'data' => new ManagerResource($manager),
            ]);
        }

        return redirect()->route('managers.index');
    }

    public function activities(Request $request, Manager $manager)
    {
        if ($request->ajax()) {
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

        return view('manager::panel.activity', compact('manager'));
    }

    public function getLog(Manager $manager, ManagerActivity $activity): JsonResponse
    {
        return response()->json([
            'data' => new ManagerActivityResource($activity)
        ]);
    }

    private function currentUserIsAdmin(): bool
    {
        return Auth::guard('manager')->user()?->role === ManagerRoleEnum::Admin;
    }

    private function getAvailableRoles(bool $isAdmin): array
    {
        $roles = ManagerRoleEnum::toArray();

        return $isAdmin ? $roles : array_diff($roles, [ManagerRoleEnum::Admin->value]);
    }

    private function getGroupedPermissions(): array
    {
        return Permission::all()->reduce(function ($grouped, $permission) {
            $prefix = Str::before($permission->key, '.');

            $grouped[$prefix][$permission->id] = $permission->display_name;

            return $grouped;
        }, []);
    }
}
