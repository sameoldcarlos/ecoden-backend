<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class BoardController extends Controller
{
    protected $user;
    public function __construct()
    {
        // dd(JWTAuth::parseToken()->authenticate());
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $exception) {
            return response()->json();
        }
        return $this->user;
    }
    public function redirect($str_id, $token) {
        if(!$token) {
            return response()->json([
                'error_message' => 'provide a valid access token'
            ], 401);
        }

        return redirect()->away('http://127.0.0.1:8080/boards/'.$str_id.'?token='.$token);
    }
}
