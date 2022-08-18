<?php

namespace App\Models;
use App\Models\ConfiguracaoAutor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    use HasFactory;
    protected $table = 'authors';

    public function Configuarcao()
    {
        return $this->belongsTo(App\ConfiguracaoAutor::class, 'author_id' ,'author_id');
    }
}
