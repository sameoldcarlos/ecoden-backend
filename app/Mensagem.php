<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
    protected $table = 'mensagens';
    protected $fillable = ['sender_id', 'receiver_id', 'story_id', 'type', 'status'];
}
