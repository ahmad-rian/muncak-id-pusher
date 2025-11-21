<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Laravel\Facades\Image;
use Laravel\Socialite\Facades\Socialite;
use Spatie\SchemaOrg\Schema;

class UserController extends Controller
{
    public function signIn()
    {
        $website = Schema::WebSite()
            ->url(url('/auth/sign-in'))
            ->name('muncak.id')
            ->description('Masuk ke akun muncak.id untuk mengakses informasi jalur pendakian, rute, dan perencanaan perjalanan Anda.')
            ->mainEntityOfPage(Schema::WebPage()->url(url('/auth/sign-in')));

        $schemaOrg = $website->toScript();

        return view('auth.sign-in', [
            'schemaOrg' => $schemaOrg
        ]);
    }

    public function signInAction(LoginRequest $req)
    {
        $req->authenticate();
        $req->session()->regenerate();

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();

        return redirect()->route(
            $user->hasRole('admin')
                ? 'admin.dashboard.index'
                : 'index'
        );
    }

    public function signUp()
    {
        $website = Schema::WebSite()
            ->url(url('/auth/sign-up'))
            ->name('muncak.id')
            ->description('Daftar akun muncak.id untuk mendapatkan akses ke informasi jalur pendakian, rute perjalanan, dan fitur lainnya.')
            ->mainEntityOfPage(Schema::WebPage()->url(url('/auth/sign-up')));

        $schemaOrg = $website->toScript();

        return view('auth.sign-up', [
            'schemaOrg' => $schemaOrg
        ]);
    }

    public function signUpAction(Request $req)
    {
        $req->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $userProviderExist = UserProvider::select(['id'])
            ->where('provider_email', $req->email)
            ->first();

        if ($userProviderExist) {
            return redirect()->back()->withErrors([
                'email' => 'Email is already registered using a different sign up method. Please try signing in.'
            ])->withInput();
        }

        $req->validate([
            'email' => [Rule::unique('users')]
        ]);

        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'username' => uniqid('user_'),
        ]);
        $user->assignRole('user');

        $this->createPhotoProfile($user);

        event(new Registered($user));

        Auth::login($user, (bool) $req->remember);

        return redirect()->route('verification.notice');
    }

    public function signOut(Request $req)
    {
        Auth::guard('web')->logout();

        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return redirect()->route('index');
    }

    public function oauthRedirect(Request $req)
    {
        $req->validate([
            'oauth' => Rule::in(['google', 'facebook'])
        ]);

        return Socialite::driver($req->oauth)->redirect();
    }

    public function oauthGoogleCallback()
    {
        $oauthUser = Socialite::driver('google')->user();
        $provider = 'google';
        $id = $oauthUser->getId();
        $name = $oauthUser->name;
        $email = $oauthUser->email;
        $token = $oauthUser->token;
        $refreshToken = $oauthUser->refreshToken;

        $userExist = User::where('email', $email)->first();

        if ($userExist) {
            UserProvider::updateOrCreate(
                ['user_id' => $userExist->id, 'provider' => $provider],
                [
                    'provider_email' => $email,
                    'provider_name' => $name,
                    'provider_id' => $id,
                    'provider_token' => $token,
                    'provider_refresh_token' => $refreshToken,
                ]
            );

            Auth::login($userExist, true);
            return redirect()->route('index');
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'username' => uniqid('user_'),
            ]);
            $user->assignRole('user');
            $this->createPhotoProfile($user);

            UserProvider::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_email' => $email,
                'provider_name' => $name,
                'provider_id' => $id,
                'provider_token' => $token,
                'provider_refresh_token' => $refreshToken,
            ]);

            DB::commit();

            Auth::login($user, true);

            return redirect()->route('index');
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            return redirect()->route('auth.sign-in')->withErrors(['error' => 1]);
        }
    }

    public function oauthFacebookCallback()
    {
        $oauthUser = Socialite::driver('facebook')->user();
        $provider = 'facebook';
        $id = $oauthUser->getId();
        $name = $oauthUser->name;
        $email = $oauthUser->email;
        $token = $oauthUser->token;
        $refreshToken = $oauthUser->refreshToken;

        $userExist = User::where('email', $email)->first();

        if ($userExist) {
            UserProvider::updateOrCreate(
                ['user_id' => $userExist->id, 'provider' => $provider],
                [
                    'provider_email' => $email,
                    'provider_name' => $name,
                    'provider_id' => $id,
                    'provider_token' => $token,
                    'provider_refresh_token' => $refreshToken,
                ]
            );

            Auth::login($userExist, true);
            return redirect()->route('index');
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'username' => uniqid('user_'),
            ]);
            $user->assignRole('user');
            $this->createPhotoProfile($user);

            UserProvider::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_email' => $email,
                'provider_name' => $name,
                'provider_id' => $id,
                'provider_token' => $token,
                'provider_refresh_token' => $refreshToken,
            ]);

            DB::commit();

            Auth::login($user, true);

            return redirect()->route('index');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('auth.sign-in')->withErrors(['error' => 1]);
        }
    }

    /**
     * API
     */
    public function apiToggleTheme(Request $request)
    {
        $currentTheme = $request->session()->get('theme', 'winter');
        $newTheme = $currentTheme === 'winter' ? 'dark-winter' : 'winter';
        $request->session()->put('theme', $newTheme);
        return response()->json(['theme' => $newTheme]);
    }

    /**
     * Private & Non Route
     */
    public function createPhotoProfile($user)
    {
        config(['media-library.temporary_upload_model' => null]);

        $parts    = explode(' ', string: $user->name);
        $initials = '';

        foreach ($parts as $part) {
            if (!empty($part) && strlen($initials) < 2) {
                $initials .= strtoupper($part[0]);
            }
        }

        $colors = ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e'];

        $randomColor = $colors[array_rand($colors)];

        $img = Image::create(300, 300)->fill($randomColor);
        $img->text($initials, 150, 150, function ($font) {
            $font->file(storage_path('fonts/Merriweather-Black.ttf'));
            $font->size(100);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });

        $filePath = 'temp/' . uniqid('photo-profile-') . '.png';

        $encodedImage = $img->encodeByExtension('png');
        Storage::put($filePath, $encodedImage);

        if (!$user->getFirstMedia('photo-profile')) {
            $user->addMedia(storage_path("app/private/$filePath"))->toMediaCollection('photo-profile');
        }

        Storage::delete($filePath);
    }
}
