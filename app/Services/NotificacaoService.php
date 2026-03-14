<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificacaoService
{
    protected $messaging;

    public function __construct()
    {
        if (app()->bound(Messaging::class)) {
            $this->messaging = app(Messaging::class);
        }
    }

    /**
     * Envia uma notificação para um usuário específico
     *
     * @param User $user
     * @param string $titulo
     * @param string $corpo
     * @param array $dados Adicionais (tipo, id_chamado, etc)
     * @return bool
     */
    public function enviarParaUsuario(User $user, string $titulo, string $corpo, array $dados = []): bool
    {
        if (!$this->messaging || !$user->fcm_token) {
            return false;
        }

        try {
            $notification = Notification::create($titulo, $corpo);
            
            // Garantir que todos os valores no array 'data' sejam strings
            $dadosFormatados = [];
            foreach ($dados as $key => $value) {
                $dadosFormatados[(string)$key] = (string)$value;
            }

            $message = CloudMessage::new()
                ->withToken($user->fcm_token)
                ->withNotification($notification)
                ->withData($dadosFormatados);

            $this->messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            \Log::error("Erro ao enviar notificação FCM para o usuário {$user->id}: " . $e->getMessage());
            return false;
        }
    }
}
