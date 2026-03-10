<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacaoTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'plataforma' => 'required|string|in:android,ios',
        ]);

        $user = Auth::user();
        $user->fcm_token = $request->token;
        $user->plataforma = $request->plataforma;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Token registrado com sucesso'
        ]);
    }
}
