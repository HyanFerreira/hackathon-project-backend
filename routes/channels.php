<?php

use App\Models\Aluno;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado de cada aluno — recebe convites e atualizações de desafios.
Broadcast::channel('aluno.{id}', function ($user, $id) {
    return $user instanceof Aluno && (int) $user->id === (int) $id;
});

// Canal privado da turma — alunos da turma e professores vinculados recebem a sessão ao vivo.
Broadcast::channel('turma.{id}', function ($user, $id) {
    if ($user instanceof Aluno) {
        return $user->turmas()->whereKey($id)->exists();
    }

    return $user instanceof User && $user->turmas()->whereKey($id)->exists();
});

// Canal privado do professor — painel da sessão recebe desempenho em tempo real.
Broadcast::channel('professor.{id}', function ($user, $id) {
    return $user instanceof User
        && (int) $user->id === (int) $id
        && $user->hasRole('professor');
});
