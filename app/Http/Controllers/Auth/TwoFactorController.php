<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorControllerIndexRequest;
use App\Http\Requests\TwoFactorStoreRequest;
use App\Models\Configuration;
use App\Notifications\TwoFactorCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Guid\Guid;

class TwoFactorController extends Controller
{
    private int $lifetimeInMinutes = 60 * 24 * 7;

    public function index(TwoFactorControllerIndexRequest $request): mixed
    {
        if ($this->isDeviceRemembered($request) || $this->isTwoFactorDisabled()) {
            return redirect('/');
        }

        $user = $request->user();

        if ($user->two_factor_expires_at && $user->two_factor_expires_at->isPast()) {
            return $this->resetTwoFactorCodeAndRedirectToLogin($user);
        }

        if ($request->has('two_factor_code')) {
            if ($user->two_factor_code === $request->input('two_factor_code')) {
                $user->resetTwoFactorCode();
                return redirect('/')->withCookie(cookie('device_guid', Guid::uuid4(), $this->lifetimeInMinutes));
            }
            return $this->resetTwoFactorCodeAndRedirectToLogin($user);
        }

        if ($user->two_factor_code === null) {
            $user->generateTwoFactorCode();

            dispatch(function () use ($user) {
                $user->notify(new TwoFactorCode());
            })->afterResponse();
        }

        return view('auth.twoFactor');
    }

    public function store(TwoFactorStoreRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->input('two_factor_code') !== $user->two_factor_code) {
            return $this->resetTwoFactorCodeAndRedirectToLogin($user);
        }

        $user->resetTwoFactorCode();

        return redirect('/')->withCookie(cookie('device_guid', Guid::uuid4(), $this->lifetimeInMinutes));
    }

    private function isDeviceRemembered($request): bool
    {
        return $request->cookie('device_guid') !== null;
    }

    private function isTwoFactorDisabled(): bool
    {
        return Configuration::first('disable_2fa')->disable_2fa;
    }

    private function resetTwoFactorCodeAndRedirectToLogin($user): RedirectResponse
    {
        Auth::logoutCurrentDevice();
        $user->resetTwoFactorCode();
        return redirect()->route('login')->withErrors(['two_factor_code' => 'Invalid code']);
    }
}
