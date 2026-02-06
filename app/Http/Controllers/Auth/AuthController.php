<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\UserRequest;
use App\Http\Services\Common\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $authService;

    public function __construct(AuthService $authService,)
    {
        $this->authService = $authService;
    }
    public function registration(UserRequest $request)
    {
        return $this->authService->registration($request);
    }
    // Login User
    public function login(Request $request)
    {
        return $this->authService->login($request);
    }



    // Reset Password
    public function resetPassword(Request $request)
    {
        return $this->authService->resetPassword($request);
    }

    // Update Password
    public function updatePassword(Request $request)
    {
        return $this->authService->updatePassword($request);
    }


    public function createTrainer(Request $request)
    {
        return $this->authService->createTrainer($request);
    }

    public function trainerList(Request $request)
    {
        return $this->authService->trainerList($request);
    }

    public function groupTrainerList(Request $request)
    {
        return $this->authService->groupTrainerList($request);
    }

    //user list
    public function userList(Request $request)
    {
        return $this->authService->userList($request);
    }

    //user details
    public function show(Request $request)
    {
        return $this->authService->show($request);
    }

    //admin deleting user
    public function delete(Request $request)
    {
        return $this->authService->delete($request);
    }

}
