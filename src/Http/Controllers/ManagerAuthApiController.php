<?php

namespace Esanj\Manager\Http\Controllers;

use Esanj\AuthBridge\Exceptions\AuthBridgeException;
use Esanj\AuthBridge\Services\AuthBridgeService;
use Esanj\AuthBridge\Services\ClientCredentialsService;
use Esanj\Manager\Exceptions\ManagerAccessDenied;
use Esanj\Manager\Http\Request\ManagerAuthRequest;
use Esanj\Manager\Http\Request\ManagerVerifyRequest;
use Esanj\Manager\Models\Manager;
use Esanj\Manager\Services\ManagerAuthService;
use Esanj\Manager\Services\ManagerService;
use Illuminate\Http\JsonResponse;

class ManagerAuthApiController extends BaseController
{
    public function __construct(
        protected ManagerService           $managerService,
        protected ManagerAuthService       $authService,
        protected AuthBridgeService        $bridgeService,
        protected ClientCredentialsService $clientCredentialsService,
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
        ], 302);
    }

    /**
     * Verify authorization code from the Bridge and prepare login info.
     */
    public function verifyAuthorizationCode(ManagerVerifyRequest $request): JsonResponse
    {
        $authCode = $request->input('code');

        try {
            $response = $this->bridgeService->exchangeAuthorizationCodeForAccessToken($authCode);

            $decoded = $this->clientCredentialsService->extractJwt($response->accessToken);
        } catch (AuthBridgeException $e) {
            return $this->response($e->getMessage(), false, $e->getCode());
        }

        try {
            $manager = $this->getManager($decoded->sub);

            return response()->json([
                'status' => true,
                'data' => [
                    'requires_token' => $manager->needToken(),
                    'auth_code' => $response->accessToken,
                ],
            ]);

        } catch (ManagerAccessDenied $e) {
            return $this->response($e->getMessage(), false, $e->getCode());
        }
    }

    /**
     * Authenticate manager using auth_code and optional token from Bridge.
     */
    public function authenticateFromBridge(ManagerAuthRequest $request): JsonResponse
    {
        $authCode = $request->input('auth_code');
        $token = $request->input('token', "");

        try {
            $decoded = $this->clientCredentialsService->extractJwt($authCode);
        } catch (AuthBridgeException $e) {
            return $this->response($e->getMessage(), false, $e->getCode());
        }

        try {
            $manager = $this->getManager($decoded->sub);

            if ($manager->needToken()) {
                $this->managerService->checkManagerToken($manager, $token);
            }

        } catch (ManagerAccessDenied $e) {
            $this->authService->hitRateLimit();
            return $this->response($e->getMessage(), false, $e->getCode());
        }


        $this->managerService->updateLastLogin($manager->id);

        $this->managerService->logActivityFor($manager, 'manager.login', [
            'login_type' => 'api',
        ]);

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

    /**
     * @throws ManagerAccessDenied
     */
    private function getManager(int $esanj_id): Manager
    {
        $manager = $this->managerService->findByEsanjId($esanj_id);

        if (!$manager) {
            throw ManagerAccessDenied::managerNotFound();
        } elseif (!$manager->isActive()) {
            throw ManagerAccessDenied::managerNotActive();
        }

        return $manager;
    }

    private function response(string $message, bool $success = true, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
        ], $code);
    }
}
