<?php
 
namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    // POST /api/password/forgot
    public function sendResetLink(Request $request)
    {
        $data = $request->validate(['email' => ['required','email']]);

        // Ne otkrivamo da li email postoji (uvek 200)
        $user = User::where('email', $data['email'])->first();
        if ($user) {
            // kreira (ili osveži) token u password_reset_tokens
            $token = Password::createToken($user);

            $resetUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')), '/')
                . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user->email);

            Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));
        }

        return response()->json(['message' => 'Ako nalog postoji, poslat je mejl sa uputstvom.']);
    }

    // POST /api/password/reset
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email'                 => ['required','email'],
            'token'                 => ['required','string'],
            'password'              => ['required', PasswordRule::min(8), 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::reset(
            [
                'email'                 => $data['email'],
                'token'                 => $data['token'],
                'password'              => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // Opozovi postojeće API tokene nakon reset-a
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Lozinka je uspešno resetovana.']);
        }

        // Tipični statusi: PASSWORD_RESET, INVALID_TOKEN, INVALID_USER, PASSWORD_RESET_THROTTLED
        return response()->json(['message' => __($status)], 422);
    }
}
