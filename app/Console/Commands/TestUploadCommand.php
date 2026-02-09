<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestUploadCommand extends Command
{
    protected $signature = 'test:upload';
    protected $description = 'Comando para testar upload de arquivos';

    public function handle()
    {
        try {
            // Testar se o disco público está funcionando
            $disk = Storage::disk('public');
            
            // Verificar se o diretório existe
            $directory = 'cobrancas/anexos';
            if (!$disk->exists($directory)) {
                $disk->makeDirectory($directory);
                $this->info("Diretório $directory criado com sucesso.");
            } else {
                $this->info("Diretório $directory já existe.");
            }
            
            // Criar um arquivo de teste
            $testContent = "Arquivo de teste criado em " . date('Y-m-d H:i:s');
            $fileName = 'teste_upload_' . time() . '.txt';
            $filePath = $directory . '/' . $fileName;
            
            if ($disk->put($filePath, $testContent)) {
                $this->info("Arquivo $fileName criado com sucesso em $filePath");
                
                // Verificar se o arquivo foi realmente criado
                if ($disk->exists($filePath)) {
                    $this->info("Verificação: Arquivo existe no caminho especificado.");
                    $this->info("Tamanho do arquivo: " . $disk->size($filePath) . " bytes");
                } else {
                    $this->error("ERRO: Arquivo não encontrado após criação.");
                }
            } else {
                $this->error("ERRO: Falha ao criar o arquivo $fileName");
            }
            
        } catch (\Exception $e) {
            $this->error("ERRO: " . $e->getMessage());
        }
    }
}