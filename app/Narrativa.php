<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Narrativa extends Model
{
    protected $fillable = ['title', 'code', 'diagram', 'str_id', 'screenshot'];
    public function estudantes()
    {
        return $this->belongsToMany(Estudante::class)->withPivot('is_author');
    }
}
