<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\Manager\Enums\ManagerRoleEnum;
use Esanj\Manager\Http\Middleware\CheckManagerPermissionMiddleware;
use Esanj\Manager\Http\Request\ManagerCreateRequest;
use Esanj\Manager\Http\Request\ManagerUpdateRequest;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Models\Permission;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManagerController extends BaseController
{
    public function __construct(protected ManagerService $managerService)
    {
        $this->middleware(CheckManagerPermissionMiddleware::class . ':managers.list')->only(['index']);
        $this->middleware(CheckManagerPermissionMiddleware::class . ':managers.create')->only(['create', 'store']);
        $this->middleware(CheckManagerPermissionMiddleware::class . ':managers.edit')->only(['edit', 'update']);
    }

    public function index(): View
    {
        return view('manager::panel.index');
    }

    public function create(): View
    {
        $isAdmin = $this->currentUserIsAdmin();

        $roles = $this->getAvailableRoles($isAdmin);
        $permissions = $this->getGroupedPermissions();
        $token = $this->managerService->generateToken(config('esanj.manager.token_length'));

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

        $requestData = $request->only(['name', 'esanj_id', 'role', 'is_active']);
        $requestData['token'] = $request->input('token') ?? $this->managerService->generateToken(config('esanj.manager.token_length'));

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
        $updateData = $request->only(['role', 'is_active', 'name']);

        if ($request->filled('token')) {
            $updateData['token'] = $request->input('token');
        }

        $this->managerService->updateManager($manager->id, $updateData);

        $manager->permissions()->sync($request->input('permissions', []));

        return redirect()->route('managers.edit', $manager->id)
            ->with('success', trans('manager::manager.success.updated'));
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
