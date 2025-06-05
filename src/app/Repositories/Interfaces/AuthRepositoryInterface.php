<?php

namespace App\Repositories\Interfaces;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    public function register(RegisterRequest $request);
    public function login(Request $request);
    public function logout(Request $request);
}
