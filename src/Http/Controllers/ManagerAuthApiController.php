<?php

namespace Esanj\Manager\Http\Controllers;

use App\Http\Controllers\Controller;
use Esanj\AuthBridge\Services\AuthBridgeService;
use Esanj\Manager\Http\Request\ManagerAuthRequest;
use Esanj\Manager\Http\Request\ManagerVerifyRequest;
use Esanj\Manager\Services\ManagerAuthService;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\JsonResponse;

class ManagerAuthApiController extends Controller
{
    public function __construct(
        protected ManagerService     $managerService,
        protected ManagerAuthService $authService,
        protected AuthBridgeService  $bridgeService,
    )
    {
    }

    /**
     * Redirect managers to the Auth Bridge page.
     */
    public function redirectToBridge(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'redirect_url' => route('auth-bridge.redirect'),
            ],
        ], 301);
    }

    /**
     * Verify authorization code from the Bridge and prepare login info.
     */
    public function verifyAuthorizationCode(ManagerVerifyRequest $request): JsonResponse
    {
        $authCode = $request->get('code');
        $response = $this->bridgeService->exchangeAuthorizationCodeForAccessToken($authCode);

        $responseData = $response->json();

        if (!$response->successful()) {
            $message = $responseData['error_description'] ?? __('manager::manager.errors.invalid_request');
            return response()->json(['message' => $message], 403);
        }

        $esanjId = $this->authService->extractEsanjIdFromJwt($responseData['access_token']);
        $manager = $this->managerService->findByEsanjId($esanjId ?? 0);

        if (!$manager) {
            return response()->json(['message' => __('manager::manager.errors.token_incorrect')], 400);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'requires_token' => $manager->uses_token,
                'auth_code' => $responseData['access_token'],
            ],
        ]);
    }

    /**
     * Authenticate manager using auth_code and optional token from Bridge.
     */
    public function authenticateFromBridge(ManagerAuthRequest $request): JsonResponse
    {
        $authCode = $request->string('auth_code')->value();
        $token = $request->string('token', '')->value();

        $esanjId = $this->authService->extractEsanjIdFromJwt($authCode);
        if (!$esanjId) {
            return response()->json(['message' => __('manager::manager.errors.token_expired')], 400);
        }

        $manager = $this->managerService->findByEsanjId($esanjId);
        if (!$manager) {
            $this->authService->hitRateLimit();
            return response()->json(['message' => __('manager::manager.errors.token_incorrect')], 401);
        }

        if ($manager->uses_token && !$this->managerService->checkManagerToken($manager, $token)) {
            $this->authService->hitRateLimit();
            return response()->json(['message' => __('manager::manager.errors.token_incorrect')], 401);
        }

        if (!$manager->is_active) {
            return response()->json(['message' => __('manager::manager.errors.manager_not_active')], 400);
        }

        $this->managerService->updateLastLogin($manager->id);

        $accessData = $this->authService->generateAccessToken($manager);

        return response()->json([
            'status' => true,
            'data' => [
                'access_token' => $accessData['access_token'],
                'token_type' => 'Bearer',
                'expires_in' => $accessData['expires_in'],
            ],
        ]);
    }
}
