<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    /**
     * Login the user
     *
     * @param String $email
     * @param String $password
     * @return String | Exception
     */
    public function login(String $email, String $password): String | Exception
    {
        try {
            $user = User::where('email', $email)->first();
            if (!$user) {
                throw new Exception('User not found', Response::HTTP_NOT_FOUND);
            }

            if (!Hash::check($password, $user->password)) {
                throw new Exception('Invalid password', Response::HTTP_UNAUTHORIZED);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return $token;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Register the user
     *
     * @param String $name
     * @param String $email
     * @param String $password
     * @return String | Exception
     */
    public function register(String $name, String $email, String $password): String | Exception
    {
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return $token;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
