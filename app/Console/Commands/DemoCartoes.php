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
        $logo = $this->logoBase64();
        $conquistas = $this->conquistasBase64();
        $totalConquistas = count($conquistas);

        $alunos = $turma->alunos()->orderBy('nome')->get()->values()->map(function ($aluno, $i) use ($base, $conquistas, $totalConquistas) {
            $url = "{$base}/login/estudante?codigo={$aluno->codigo}";
            $png = QrCode::format('png')->size(320)->margin(1)->errorCorrection('M')->generate($url);

            return [
                'nome' => $aluno->nome,
                'codigo' => $aluno->codigo,
                'qr' => 'data:image/png;base64,'.base64_encode($png),
                'conquista' => $totalConquistas > 0 ? $conquistas[$i % $totalConquistas] : null,
            ];
        });

        $pdf = Pdf::loadView('demo.cartoes', [
            'alunos' => $alunos,
            'logo' => $logo,
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

    /**
     * Logo do sistema (SVG) rasterizada em PNG base64 (fundo transparente).
     */
    private function logoBase64(): string
    {
        $img = new \Imagick;
        $img->setBackgroundColor(new \ImagickPixel('transparent'));
        $img->readImage(public_path('demo/logo.svg'));
        $img->setImageFormat('png');
        $img->resizeImage(520, 0, \Imagick::FILTER_LANCZOS, 1);

        return 'data:image/png;base64,'.base64_encode($img->getImageBlob());
    }

    /**
     * Rasteriza as conquistas (SVG pixel art) em PNG base64, uma será usada por cartão.
     *
     * @return list<string>
     */
    private function conquistasBase64(): array
    {
        $arquivos = glob(public_path('demo/conquistas').'/ac*.svg') ?: [];
        sort($arquivos);

        $imagens = [];
        foreach ($arquivos as $arquivo) {
            try {
                $img = new \Imagick;
                $img->setBackgroundColor(new \ImagickPixel('transparent'));
                $img->readImage($arquivo);
                $img->setImageFormat('png');
                $img->resizeImage(160, 160, \Imagick::FILTER_LANCZOS, 1, true);
                $imagens[] = 'data:image/png;base64,'.base64_encode($img->getImageBlob());
                $img->clear();
            } catch (\Throwable $e) {
                $this->warn("Falha ao processar {$arquivo}: {$e->getMessage()}");
            }
        }

        return $imagens;
    }
}
