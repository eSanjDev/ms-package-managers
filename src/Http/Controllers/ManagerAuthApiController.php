<?php

namespace Esanj\Manager\Http\Controllers;

use App\Http\Controllers\Controller;
use Esanj\Manager\Http\Request\ManagerAuthRequest;
use Esanj\Manager\Http\Request\ManagerVerifyRequest;
use Esanj\Manager\Services\ManagerAuthService;
use Esanj\Manager\Services\ManagerService;

class ManagerAuthApiController extends Controller
{
    public function __construct(
        protected ManagerService     $managerService,
        protected ManagerAuthService $managerAuthService)
    {
    }

    public function redirectToAuthBridge()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'redirect_url' => route('auth-bridge.redirect')
            ]
        ], 301);
    }

    public function verifyManagerCode(ManagerVerifyRequest $request)
    {
        $esanjId = $this->managerAuthService->extractEsanjIdFromJwt($request->get('code'));
        $manager = $this->managerService->findByEsanjId($esanjId ?? 0);

        if (!$manager) {
            return response()->json([
                'message' => trans('manager::manager.errors.token_incorrect')
            ], 400);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'is_valid' => true,
                'requires_token' => $manager->uses_token,
            ]
        ]);
    }

    public function authenticateViaBridge(ManagerAuthRequest $request)
    {
        $code = $request->input('code');
        $token = $request->input('token', '');

        $esanjId = $this->managerAuthService->extractEsanjIdFromJwt($code);
        if (!$esanjId) {
            return response()->json([
                'message' => trans('manager::manager.errors.token_expired')
            ], 400);
        }

        $manager = $this->managerService->findByEsanjId($esanjId);

        if (!$manager || ($manager->uses_token && !$this->managerService->checkManagerToken($manager, $token))) {
            $this->managerAuthService->hitRateLimit();
            return response()->json([
                'message' => trans('manager::manager.errors.token_incorrect'),
            ], 401);
        }

        if (!$manager->is_active) {
            return response()->json([
                'message' => trans('manager::manager.errors.manager_not_active')
            ], 400);
        }

        $this->managerService->updateLastLogin($manager->id);

        $getAccessToken = $this->managerAuthService->generateAccessToken($manager);

        return response()->json([
            'status' => true,
            'data' => [
                'access_token' => $getAccessToken['access_token'],
                'token_type' => 'Bearer',
                'expires_in' => $getAccessToken['expires_in'],
            ]
        ]);
    }

}
