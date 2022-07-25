<?php

namespace App\Http\Controllers;

use App\Estudante;
use App\Mensagem;
use App\Narrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class MensagemController extends Controller
{
    protected $user;
    public function __construct()
    {
        try {
            $this->user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $exception) {
            return response()->json();
        }
        return $this->user;
    }

    public function showInvites($id) {
        $estudante = Estudante::find($id);
        if(!$this->user || $this->user->id != $id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $mensagens = Mensagem::where('receiver_id', $id)->where('type', 'invite')->where('status', 'pending')->paginate(10);
        foreach ($mensagens as $mensagem) {
            $sender_email = Estudante::find($mensagem->sender_id)->email;
            $story_title = Narrativa::find($mensagem->story_id)->title;
            $mensagem->sender_email = $sender_email;
            $mensagem->story_title = $story_title;
        }
        return response()->json([
            'convites' => $mensagens
        ], 200);
    }

    public function showEditRequests($id)
    {
        $estudante = Estudante::find($id);
        if (!$this->user || $this->user->id != $id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $mensagens = Mensagem::where('receiver_id', $id)->where('type', 'request')->where('status', 'pending')->paginate(10);
        // para cada mensagem, pegar o titulo da narrativa e o email do remetente
        foreach($mensagens as $mensagem) {
            $sender_email = Estudante::find($mensagem->sender_id)->email;
            $story_title = Narrativa::find($mensagem->story_id)->title;
            $mensagem->sender_email = $sender_email;
            $mensagem->story_title = $story_title;
        }
        return response()->json([
            'solicitacoes' => $mensagens
        ], 200);
    }

    public function inviteUsers(Request $request, $narrativa_id) {
        $narrativa = Narrativa::find($narrativa_id);
        $auth_id = $this->user->id;
        $editors = $narrativa->estudantes;
        $canUserEditStory = $editors->find($auth_id);

        if (!$canUserEditStory) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $not_invited_users = [];
        foreach($request->receiver_emails as $receiver_email) {
            $new_editor = Estudante::where('email', $receiver_email)->first();
            
            if(!$new_editor || $receiver_email == $this->user->email) {
                array_push($not_invited_users, $receiver_email);
                continue;
            }

            $invite = new Mensagem();
            
            $invite->fill([
                'sender_id' => $this->user->id,
                'receiver_id' => $new_editor->id,
                'story_id' => $narrativa_id,
                'type' => 'invite',
                'status' => 'pending'
            ]);

            $invite->save();
        }

        return response()->json([
            'message' => 'Invites sent',
            'not_invited_users' => $not_invited_users
        ], 201);
    }

    public function requestAccess($narrativa_id)
    {
        $narrativa = Narrativa::find($narrativa_id);
        
        if (!$narrativa) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        $author_id = $narrativa->estudantes()->where('is_author', 1)->first()->id;
        $auth_id = $this->user->id;

        // $requestExist = Narrativa::where('sender_id', $auth_id)->where('receiver_id', $author_id)->where('story_id', $narrativa_id)->where('type', 'request')->where('status', 'pending')
        $requestExist = Mensagem::where([
            ['sender_id', '=', $auth_id],
            ['receiver_id', '=', $author_id],
            ['story_id', '=', $narrativa_id],
            ['type', '=', 'request'],
            ['status', '=', 'pending']
        ])->first();

        if($requestExist) {
            return response()->json([
                'message' => 'Já existe uma requisição pendente para esta narrativa.'
            ], 409);
        }

        $edit_request = new Mensagem();
        $edit_request->fill([
            'sender_id' => $auth_id,
            'receiver_id' => $author_id,
            'story_id' => $narrativa_id,
            'type' => 'request',
            'status' => 'pending'
        ]);

        $edit_request->save();
        return response()->json([
            'solicitacao' => $edit_request,
            'message' => 'Solicitacao enviada'
        ], 201);
    }

    public function acceptInvite($invite_id)
    {
        $invite = Mensagem::find($invite_id);
        $narrativa = Narrativa::find($invite->story_id);
        
        if (!$invite || !$narrativa) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        if($invite->receiver_id != $this->user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $estudante = Estudante::find($invite->receiver_id);
        $narrativa->estudantes()->attach($estudante);

        $invite->status = 'accepted';
        $invite->save();

        return response()->json([
            'message' => 'Editor successfully attached',
            'convites' => Mensagem::where('receiver_id', $this->user->id)->where('type', 'invite')->where('status', 'pending')->paginate(10)
        ], 200);

    }

    public function acceptRequest($request_id)
    {
        $edit_request = Mensagem::find($request_id);
        $narrativa = Narrativa::find($edit_request->story_id);

        if (!$edit_request || !$narrativa) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        if ($edit_request->receiver_id != $this->user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $estudante = Estudante::find($edit_request->sender_id);
        $narrativa->estudantes()->attach($estudante);

        $edit_request->status = 'accepted';
        $edit_request->save();

        return response()->json([
            'message' => 'Editor successfully attached',
            'solicitacoes' => Mensagem::where('receiver_id', $this->user->id)->where('type', 'request')->where('status', 'pending')->paginate(10)
        ], 200);
    }

    public function refuseInvite($invite_id)
    {
        $invite = Mensagem::find($invite_id);

        if (!$invite) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        if ($invite->receiver_id != $this->user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $invite->status = 'refused';
        $invite->save();

        return response()->json([
            'message' => 'Invitation refused',
        ], 200);
    }

    public function refuseRequest($request_id)
    {
        $edit_request = Mensagem::find($request_id);

        if (!$edit_request) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        if ($edit_request->receiver_id != $this->user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $edit_request->status = 'refused';
        $edit_request->save();

        return response()->json([
            'message' => 'Edit Request successfully refused',
        ], 200);
    }

    public function createInvitationLink($narrativa_id)
    {
        $narrativa = Narrativa::find($narrativa_id);
        $auth_id = $this->user->id;
        $editors = $narrativa->estudantes;
        $canUserEditStory = $editors->find($auth_id);

        if(!$canUserEditStory) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $invite_str = $this->user->id.':'.$narrativa_id;
        $invite = Crypt::encrypt($invite_str);
        
        return response()->json([
            'invitation' => $invite
        ], 200);
    }

    public function acceptInvitationLink($invitation)
    {
        $invite_str = Crypt::decrypt($invitation);
        list($sender_id, $narrativa_id) = explode(':', $invite_str);
        $narrativa = Narrativa::find($narrativa_id);
        $new_editor = Estudante::find($this->user->id);

        if(!$narrativa || !$new_editor) {
            return response()->json([
                'message' => 'Record not found'
            ], 404);
        }

        $narrativa->estudantes()->attach($new_editor);

        return response()->json([
            'message' => 'Editor successfully attached',
        ], 200);
    }
}
