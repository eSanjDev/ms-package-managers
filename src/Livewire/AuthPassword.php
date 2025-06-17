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
            $this->addError('token', AuthManagerStatusResponsesEnum::to_many_attempts->value);
        }
    }

    public function updateSession(int $id): void
    {
        session()->put('auth_manager.manager_id', $id);
    }

    public function submit(OAuthService $OAuthService, ManagerService $ManagerService)
    {
        $this->validate([
            'token' => 'required|string',
        ]);

        $this->t();

        $accessToken = session('auth_manager.access_token');

        if (!$accessToken) {
            return $this->addError('token', AuthManagerStatusResponsesEnum::not_found_token->value);
        }

        $authenticate = $OAuthService->authenticate($accessToken);

        $managerID = $authenticate->sub;

        $manager = $ManagerService->findByManagerID($managerID);

        if (!$ManagerService->checkManagerToken($manager, $this->token)) {
            RateLimiter::increment("auth-manager-" . request()->ip(), $this->decaySeconds);
            return $this->addError('token', AuthManagerStatusResponsesEnum::token_incorrect->value);
        }

        $this->updateSession($managerID);

        $ManagerService->updateLastLogin($manager->id);

        return redirect(config('manager.redirect_to'));
    }
}
