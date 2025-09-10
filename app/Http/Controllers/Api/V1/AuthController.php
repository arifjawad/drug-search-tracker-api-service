<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistrationRequest;
use App\Services\ResponseService;
use Exception;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    private $authService;

    /**
     * @param AuthService $authService
     */
    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(LoginRequest $request)
    {
        try {
            $token = $this->authService->login($request->validated('email'), $request->validated('password'));
            return ResponseService::apiResponse(
                Response::HTTP_OK, 'Login successful', ['token' => $token]);
        } catch (Exception $e) {
            return ResponseService::apiResponse($e->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage() ?? 'Something went wrong');
        }
    }

    public function register(RegistrationRequest $request)
    {
        try {
            $token = $this->authService->register($request->validated('name'), $request->validated('email'), $request->validated('password'));
            return ResponseService::apiResponse(
                Response::HTTP_CREATED, 'Registration successful', ['token' => $token]);
        } catch (Exception $e) {
            return ResponseService::apiResponse($e->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage() ?? 'Something went wrong');
        }
    }
}
