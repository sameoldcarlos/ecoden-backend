<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'ApiController@authenticate');
Route::post('logout', 'ApiController@logout');
Route::get('/narrativa/{str_id}', 'NarrativaController@show');
Route::get('/narrativa/{id}/title', 'NarrativaController@getStoryTitle');
Route::get('/narrativa/{id}/screenshot', 'NarrativaController@getStoryScreenshot');
Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('/narrativa/new', 'NarrativaController@store');
    Route::get('/narrativa/{id}/editors', 'NarrativaController@getStoryEditors');
    Route::put('/narrativa/update/{id}', 'NarrativaController@update');
    Route::post('/narrativa/delete/{id}', 'NarrativaController@destroy');
    Route::post('/narrativa/transfer/{id}/{new_author_id}', 'NarrativaController@changeAuthor');

    Route::get('/estudante/{id}', 'EstudanteController@show');
    Route::get('/estudante/{id}/narrativas', 'EstudanteController@listStudentStories');

    Route::get('/board/{str_id}/{token}', 'BoardController@redirect');
    
    Route::get('/convites/{id}', 'MensagemController@showInvites');
    Route::get('/solicitacoes/{id}','MensagemController@showEditRequests');
    Route::post('/narrativa/{narrativa_id}/convidar', 'MensagemController@inviteUsers');
    Route::post('/narrativa/{narrativa_id}/solicitar', 'MensagemController@requestAccess');
    Route::put('/convite/{invite_id}/aceitar', 'MensagemController@acceptInvite');
    Route::put('/convite/{invite_id}/recusar', 'MensagemController@refuseInvite');
    Route::put('/solicitacao/{request_id}/aceitar', 'MensagemController@acceptRequest');
    Route::put('/solicitacao/{request_id}/recusar', 'MensagemController@refuseRequest');
    Route::get('/convite/link/{narrativa_id}/criar', 'MensagemController@createInvitationLink');
    Route::put('/convite/link/{invitation}/aceitar', 'MensagemController@acceptInvitationLink');

});