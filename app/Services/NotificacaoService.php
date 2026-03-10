<?php

namespace App\Services;

use App\Models\User;
use Kreait\Laravel\Facades\FirebaseMessaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificacaoService
{
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
        if (!$user->fcm_token) {
            return false;
        }

        try {
            $notification = Notification::create($titulo, $corpo);
            
            // Correção para versão do SDK Kreait (usando withToken)
            $message = CloudMessage::withToken($user->fcm_token)
                ->withNotification($notification)
                ->withData($dados);

            FirebaseMessaging::send($message);
            return true;
        } catch (\Throwable $e) {
            \Log::error("Erro ao enviar notificação FCM para o usuário {$user->id}: " . $e->getMessage());
            return false;
        }
    }
}
