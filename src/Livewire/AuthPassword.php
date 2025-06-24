<?php

namespace Esanj\Manager\Livewire;


use Esanj\Manager\Enums\AuthManagerStatusResponsesEnum;
use Esanj\Manager\Services\ManagerService;
use Esanj\Manager\Services\OAuthService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Url;
use Livewire\Component;

class AuthPassword extends Component
{
    #[Url]
    public string $code = '';

    public string $token = '';

    //Rate limit params
    private int $maxAttempts = 50;
    private int $decaySeconds = 900;

    public function render()
    {
        return view('manager::livewire.auth-password');
    }

    private function t(): void
    {
        if (RateLimiter::tooManyAttempts("auth-manager-" . request()->ip(), $this->maxAttempts)) {
            $this->addError('token', AuthManagerStatusResponsesEnum::TOO_MANY_ATTEMPTS->message());
        }
    }

    public function updateSession(int $id): void
    {
        session()->put('auth_manager.manager_id', $id);
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }

    public function submit(OAuthService $OAuthService, ManagerService $ManagerService)
    {
        $this->t();

        $accessToken = session('auth_manager.access_token');

        if (!$accessToken) {
            return $this->addError('token', AuthManagerStatusResponsesEnum::TOKEN_NOT_FOUND->message());
        }

        $authenticate = $OAuthService->authenticate($accessToken);

        $managerID = $authenticate->sub;

        $manager = $ManagerService->findByManagerID($managerID);

        if (!$ManagerService->checkManagerToken($manager, $this->token)) {
            RateLimiter::increment("auth-manager-" . request()->ip(), $this->decaySeconds);
            return $this->addError('token', AuthManagerStatusResponsesEnum::TOKEN_INCORRECT->message());
        }

        $this->updateSession($managerID);

        $ManagerService->updateLastLogin($manager->id);

        return redirect(config('manager.redirect_to'));
    }
}
