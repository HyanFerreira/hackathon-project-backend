<?php

namespace App\Console\Commands;

use App\Models\Turma;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\DemoAlunosSeeder;
use Database\Seeders\EscolaSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DemoCartoes extends Command
{
    protected $signature = 'demo:cartoes {--url= : URL base do frontend (ex.: https://app.exemplo.com)} {--saida= : Caminho do PDF de saída}';

    protected $description = 'Gera um PDF (QR + código) dos alunos de demonstração para imprimir.';

    public function handle(): int
    {
        $turma = $this->turmaDemo();

        if (! $turma || $turma->alunos()->count() === 0) {
            $this->info('Criando alunos de demonstração...');
            $this->call('db:seed', ['--class' => DemoAlunosSeeder::class, '--force' => true]);
            $turma = $this->turmaDemo();
        }

        if (! $turma) {
            $this->error('Escola/turma de demonstração não encontrada. Rode o seed principal antes.');

            return self::FAILURE;
        }

        $base = rtrim($this->option('url') ?: env('FRONTEND_URL', 'http://127.0.0.1:3000'), '/');
        $mascote = 'data:image/png;base64,'.base64_encode(File::get(public_path('demo/mascote.png')));

        $alunos = $turma->alunos()->orderBy('nome')->get()->map(function ($aluno) use ($base) {
            $url = "{$base}/login/estudante?codigo={$aluno->codigo}";
            $png = QrCode::format('png')->size(320)->margin(1)->errorCorrection('M')->generate($url);

            return [
                'nome' => $aluno->nome,
                'codigo' => $aluno->codigo,
                'qr' => 'data:image/png;base64,'.base64_encode($png),
            ];
        });

        $pdf = Pdf::loadView('demo.cartoes', [
            'alunos' => $alunos,
            'mascote' => $mascote,
            'sistema' => 'Paideia',
        ])->setPaper('a4', 'portrait');

        $saida = $this->option('saida') ?: storage_path('app/demo/cartoes-alunos.pdf');
        File::ensureDirectoryExists(dirname($saida));
        $pdf->save($saida);

        $this->info("PDF gerado com {$alunos->count()} cartões:");
        $this->line($saida);
        $this->line("Deep-link usado: {$base}/login/estudante?codigo=CODIGO");

        return self::SUCCESS;
    }

    private function turmaDemo(): ?Turma
    {
        return Turma::query()
            ->where('nome', DemoAlunosSeeder::TURMA)
            ->whereHas('escola', fn ($q) => $q->where('nome', EscolaSeeder::PRINCIPAL))
            ->first();
    }
}
