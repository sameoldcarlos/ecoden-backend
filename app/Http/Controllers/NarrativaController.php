<?php

namespace App\Http\Controllers;

use App\Estudante;
use App\Narrativa;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class NarrativaController extends Controller
{
    protected $user;
    public function __construct()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $exception) {
            $this->user = null;
        }
        return $this->user;
    }

    public function show($str_id)
    {
        $narrativa = Narrativa::where('str_id', $str_id)->first();
        if (!$narrativa) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }
        $editors = $this->getStoryEditors($narrativa->id);
        $canUserEditStory = $this->canEdit($narrativa->id);
        return response()->json([
            'id' => $narrativa->id,
            'title' => $narrativa->title,
            'code' => $narrativa->code,
            'str_id' => $narrativa->str_id,
            'can_edit' => $canUserEditStory,
            'editors' => $editors
        ]);
    }

    public function store(Request $request)
    {
        $narrativa = new Narrativa();
        $narrativa->fill(
            [
                'title' => $request->title,
                'code' => $request->code,
                'screenshot' => $request->screenshot,
                'str_id' => $request->str_id
            ]
        );
        $narrativa->save();
        $author = $this->user;
        $narrativa->estudantes()->attach($author, ['is_author' => true]);
        
        return response()->json($narrativa, 201);
    }

    public function update(Request $request, $id)
    {
        if (!$this->canEdit($id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $narrativa = Narrativa::findOrFail($id);

        if (!$narrativa) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        if(!$request->screenshot || empty($request->screenshot)) {
            $request->screenshot = $narrativa->screenshot;
        }
        
        $narrativa->fill(
            [
                'title' => $request->title,
                'code' => $request->code,
                'screenshot' => $request->screenshot,
                'str_id' => $request->str_id
            ]
        );
        $narrativa->save();
        
        return response()->json($narrativa);
    }

    public function destroy($id)
    {
        $narrativa = Narrativa::findOrFail($id);
        $author_id = $narrativa->estudantes()->where('is_author', 1)->first()->id;

        if (!$narrativa) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }
        $auth_id = 0;
        if($this->user) {
            $auth_id = $this->user->id;
        }
        if (!$auth_id || $author_id != $auth_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
        $narrativa->delete();
        return response()->json([
            'message' => 'Successfully deleted',
        ], 200);
    }

    public function changeAuthor ($id, $new_author_id)
    {
        $narrativa = Narrativa::find($id);
        $author_id = $narrativa->estudantes()->where('is_author', 1)->first()->id;

        $auth_id = 0;
        if ($this->user) {
            $auth_id = $this->user->id;
        }
        if(!$auth_id || $author_id != $auth_id)
        {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $narrativa->estudantes()->detach($author_id);
        $narrativa->estudantes()->attach($author_id);
        $narrativa->estudantes()->attach($new_author_id, ['is_author' => true]);

        return response()->json($narrativa);

    }

    public function attachEditor(Request $request)
    {
        $estudante = Estudante::where('email', $request->email_editor)->first();
        $narrativa = Narrativa::find($request->id_narrativa);

        if(!$narrativa || !$estudante) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        $narrativa->estudantes()->attach($estudante);
        return response()->json([
            'message' => 'Editor successfully attached',
        ], 200);

    }

    public function getStoryTitle($id)
    {
        $narrativa = Narrativa::find($id);

        if(!$narrativa) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        return response()->json([
            'title' => $narrativa->title
        ], 200);
    }

    public function getStoryScreenshot($id) {
        $narrativa = Narrativa::find($id);

        if(!$narrativa) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        return response()->json([
            'screenshot' => $narrativa->screenshot
        ], 200);
    }
    public function getStoryEditors($id) {
        $narrativa = Narrativa::find($id);
        
        if (!$narrativa) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }
        $editors = null;
        $editors = $narrativa->estudantes;
        // dd($editors->find($auth_id));
        return $editors;
    }

    public function canEdit ($id) {
        $editors = $this->getStoryEditors($id);
        
        if ($this->user) {
            $auth_id = $this->user->id;
            $canUserEditStory = $editors->find($auth_id);

            if($canUserEditStory) {
                return true;
            }
            
            return false;
        }

        return false;
        
    }
}
