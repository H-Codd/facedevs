<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends BaseController
{
    public function __construct()
    {
        // Rotas que não exigem autenticação
        $this->middleware('auth:api', [
            'except' => ['login', 'create']
        ]);
    }

    public function unauthorized() {
        return response()->json(['error' => 'Não autorizado'], 401);
    }

    public function logout() {
        Auth::logout();
        return ['error' => ''];
    }

    public function refresh() {
        $token = Auth::refresh();
        return [
            'error' => '',
            'token' => $token
        ];
    }

    // Cadastro de usuário
    public function create(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required',
            'birthdate' => 'required|date',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'birthdate' => $request->birthdate,
        ]);

        // Gera token automaticamente após cadastro
        $token = Auth::attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'user'  => $user,
            'token' => $token
        ], 201);
    }

    // Login de usuário
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
        ]);
    }
}
