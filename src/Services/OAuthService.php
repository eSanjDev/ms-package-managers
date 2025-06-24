<?php

namespace Esanj\Manager\Services;

use Esanj\Manager\Enums\AuthManagerStatusResponsesEnum;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use stdClass;
use Throwable;

class OAuthService
{
    /**
     * Cache key prefix for OAuth state.
     *
     * @var string
     */
    protected string $stateCacheKey = 'oauth_state_';
    /**
     * Time to live for the OAuth state cache in seconds.
     *
     * @var int
     */
    protected int $stateCacheTTL;

    /**
     * Base URL for the OAuth server.
     *
     * @var string
     */
    protected string $baseUrl;

    public function __construct()
    {
        $this->stateCacheTTL = config('manager.state_ttl', 300);
        $this->baseUrl = config('manager.base_url');
    }

    /**
     * Store the OAuth state in cache.
     *
     * @param string $state
     * @return bool
     */
    public function putCacheState(string $state): bool
    {
        return Cache::put($this->stateCacheKey . $state, true, $this->stateCacheTTL);
    }

    /**
     * Check if the OAuth state exists in cache.
     *
     * @param string $state
     * @return bool
     */
    public function pullState(string $state): bool
    {
        return (bool)Cache::pull($this->stateCacheKey . $state);
    }

    /**
     * Redirect to the OAuth authorization endpoint.
     *
     * @return RedirectResponse
     */
    public function authorize()
    {
        $state = Str::random(64);
        $this->putCacheState($state);

        $query = http_build_query([
            'client_id' => config('manager.client_id'),
            'redirect_uri' => route("oauth.callback"),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            'prompt' => 'consent',
        ]);

        return redirect("{$this->baseUrl}/oauth/authorize?$query");
    }

    /**
     * Get the access token using the authorization code.
     *
     * @param string $code
     * @return Response
     */
    public function getToken(string $code): Response
    {
        return Http::post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'client_id' => config('manager.client_id'),
            'client_secret' => config('manager.secret_id'),
            'redirect_uri' => route('oauth.callback'),
            'code' => $code,
        ]);
    }

    /**
     * Authenticate the user using the provided access token.
     *
     * @throws Throwable
     */
    public function authenticate(string $accessToken): stdClass
    {
        throw_if(
            !File::exists(storage_path('oauth-public.key')),
            FileNotFoundException::class,
            AuthManagerStatusResponsesEnum::PUBLIC_KEY_NOT_FOUND->message()
        );

        $publicKey = file_get_contents(storage_path('oauth-public.key'));

        return JWT::decode($accessToken, new Key($publicKey, 'RS256'));
    }
}
