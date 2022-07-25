<?php

namespace App\Http\Controllers;

use App\Estudante;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class EstudanteController extends Controller
{
    protected $user;
    public function __construct()
    {
        // dd(JWTAuth::parseToken()->authenticate());
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch(JWTException $exception) {
            return response()->json();
        }
        return $this->user;
    }
    public function show($id)
    {
        if($id != auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $estudante = Estudante::findOrFail($id);
        return response()->json($estudante);
    }

    public function store(Request $request)
    {
        $estudante = new Estudante();
        $estudante->fill($request->all());
        $estudante->save();

        return response()->json($estudante, 201);
    }

    public function update(Request $request, $id)
    {
        $estudante = Estudante::findOrFail($id);

        if (!$estudante) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }

        if($this->user->id !== $estudante->id) {
            return response()->json([
                'message'   => 'Unauthorized',
            ], 401);
        }

        $estudante->fill($request->all());
        $estudante->save();

        return response()->json($estudante);
    }

    public function destroy($id)
    {
        $estudante = Estudante::find($id);

        if (!$estudante) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }

        $estudante->delete();
    }
    public function listStudentStories($id)
    {
        $estudante = Estudante::find($id);
        if (!$estudante) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }
        if ($this->user->id !== $estudante->id) {
            return response()->json([
                'message'   => 'Unauthorized',
            ], 401);
        }
        $storiesList = $estudante->narrativas()->paginate(10);
        return response()->json($storiesList, 200);
    }
}
