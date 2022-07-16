<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    use PasswordValidationRules;

    public function login(Request $request)
    {
        try {

            $validasi = Validator::make($request->all(), [
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unathorized!'
                ], 'Authentication Failed', 500);
            }
            // jika credential tidak match, maka error
            $user = User::where('email', $request->email)->first();

            // jika password tidak match
            if (!Hash::check($request->password, $user->password)) {
                throw new \Exception('Invalid Credentials!');
            }

            // jika berhasil, maka loginkan
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            // jika ada error diluar error login, maka akan tampil error dari sini
            return ResponseFormatter::error([
                'message' => 'Something went wrong!',
                'error' => $error,
            ], 'Authentication failed!', 500);
        }
    }

    public function register(Request $request)
    {

        try {

            $validasi = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users'],
                'phone_number' => ['unique:users'],
                'password' => $this->passwordRules(),
            ]);

            // create user
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'password' => Hash::make($request->password),
            ]);

            // kalau validasi berhasil, maka kita ambil datanya sekalian untuk login
            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');

            // jika ada error di luar register, akan muncul dari bawah ini
        } catch (Exception $error) {
            if ($validasi->fails()) {
                $val = $validasi->errors()->all();
                return ResponseFormatter::error([
                    'message' => $val[0],
                    'error' => $error
                ], 'Authentication Failed!', 500);
            }
        }
    }

    public function logout(Request $request)
    {
        // delete token dari user yang sedang login
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked!');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated!');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Success to get user profile!');
    }

    public function updatePhoto(Request $request)
    {

        // validasi untuk mengatur validasi + gambar
        $validator = Validator::make($request->all(), [

            // validasi bahwa hanya bisa image dan ukurannya tidak lebih dari 2MB
            'file' => 'required|image|max:2048',
        ]);

        // if validasi gagal
        if ($validator->fails()) {
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Failed to update profile photo!', 401);
        }

        // jika filenya ada
        if ($request->file('file')) {

            // upload foto ke folder
            $file = $request->file->store('assets/user', 'public');

            // simpan url foto ke database
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file], 'Success to update profile photo!');
        }
    }
}
