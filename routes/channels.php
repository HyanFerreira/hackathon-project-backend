<?php

use App\Models\Aluno;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado de cada aluno — recebe convites e atualizações de desafios.
Broadcast::channel('aluno.{id}', function ($user, $id) {
    return $user instanceof Aluno && (int) $user->id === (int) $id;
});
