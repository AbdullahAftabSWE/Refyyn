<?php

namespace App\Http\Controllers\Socialite;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class ProviderCallbackController extends Controller
{
    public function __invoke(string $provider)
    {
        if (!in_array($provider, ['google'])) {
            return redirect(route('login'))->withErrors(['provider' => 'Invalid provider']);
        }

        $socialUser = Socialite::driver($provider)->user();

        // First user becomes admin
        $isFirstUser = User::count() === 0;

        $user = User::updateOrCreate([
            'provider_id' => $socialUser->id,
            'provider_name' => $provider,
        ], [
            'avatar' => $socialUser->avatar,
            'name' => $socialUser->name,
            'email' => $socialUser->email,
            'provider_token' => $socialUser->token,
            'provider_refresh_token' => $socialUser->refreshToken,
            'is_admin' => $isFirstUser,
        ]);

        Auth::login($user, true);

        // Redirect admins to admin dashboard, others to feedback board
        if ($user->isAdmin()) {
            return redirect(route('admin.dashboard'));
        }

        return redirect(route('feedback'));
    }
}
