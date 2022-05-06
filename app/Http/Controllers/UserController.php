<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use App\Mail\MailResetPasswordRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Laravel APIs development => array(
 * Postman Usage,
 * Mailing,
 * Queue Jobs,
 * Sanctum for Authentication,
 * Storage
 * );
 */
class UserController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|string|max:191|email|unique:users',
            'password' => [
                'required',
                'max:150',
                'confirmed',
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
            ],
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = 'user';

        if ($user->save()) {
            return response()->json([
                'message' => 'Registration successful, please try to login'
            ], 201);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Invalid username/password'
            ], 401);
        }

        $user = $request->user();

        $user->tokens()->delete();

        if ($user->role == 'admin') {
            $token = $user->createToken('Personal Access Token', ['admin']);
        } else {
            $token = $user->createToken('Personal Access Token', ['user']);
        }

        return response()->json([
            'user' => $user,
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'abilities' => $token->accessToken->abilities
        ], 200);
    }

    public function resetPasswordRequest(Request $request) {
        $request->validate([
            'email' => 'required|string|email'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'We have sent a verification code to your provided email'
            ], 200);
        }

        $code = rand(111111, 999999);
        $user->verification_code = $code;

        if ($user->save()) {
            $emailData = array(
                'heading' => 'Reset Password Request',
                'name' => $user->name,
                'email' => $user->email,
                'code' => $user->verification_code
            );

            Mail::to($emailData['email'])->queue(new MailResetPasswordRequest($emailData));

            return response()->json([
                'message' => 'We have sent a verification code to your provided email'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'verification_code' => 'required|integer',
            'new_password' => [
                'required',
                'max:150',
                'confirmed',
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
            ],
        ]);

        $user = User::where('email', $request->email)->where('verification_code', $request->verification_code)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found/Invalid code'
            ], 404);
        }

        $user->password = bcrypt($request->new_password);
        $user->verification_code = NULL;

        if ($user->save()) {
            return response()->json([
                'message' => 'Password updated successfully!'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    public function profile(Request $request) {
        $user = $request->user();

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'max:150',
                'confirmed',
                Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
            ],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is wrong'
            ], 401);
        }

        $user->password = bcrypt($request->new_password);
        if ($user->save()) {
            return response()->json([
                'message' => 'Password changed succesfully!'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    public function updateProfile(Request $request) {
        $request->validate([
            'name' => 'required|string|max:150',
        ]);

        $user = $request->user();

        $oldPhoto = $user->photo;
        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'image|mimes:jpeg,png,jpg|max:5120',
            ]);

            $path = $request->file('photo')->store('profile');
            $user->photo = $path;
        }

        $user->name = $request->name;
        $user->about = $request->about;

        if ($user->save()) {
            if ($oldPhoto != $user->photo) {
                Storage::delete($oldPhoto);
            }

            return response()->json($user, 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    public function logout(Request $request) {
        if ($request->user()->tokens()->delete()) {
            return response()->json([
                'message' => 'Logout successfully!'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }
}
