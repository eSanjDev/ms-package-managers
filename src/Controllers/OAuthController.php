<?php

namespace Esanj\Manager\Controllers;

use App\Http\Controllers\Controller;
use Esanj\Manager\Services\OAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class OAuthController extends Controller
{
    public function __construct(protected OAuthService $service)
    {
    }

    public function redirect()
    {
        return $this->service->authorize();
    }


    public function callback(Request $request)
    {
        throw_if(
            !$this->service->pullState($request->input("state")),
            InvalidArgumentException::class,
            'Invalid state value.'
        );

        $response = $this->service->getToken($request->input("code"));

        if ($response->successful()) {
            $responseData = $response->json();

            Session::put([
                'auth_manager' => [
                    'access_token' => $responseData['access_token'],
                    'refresh_token' => $responseData['refresh_token'],
                    'expires_in' => $responseData['expires_in'],
                    'token_type' => $responseData['token_type'],
                ]
            ]);

            return view("manager::password");
        }

        return redirect()->route('oauth.redirect');
    }
}
