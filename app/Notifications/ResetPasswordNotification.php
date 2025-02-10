<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPasswordNotification
{
    protected function buildMailMessage($url)
    {
        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Redefinição de senha — '.config('app.name'))
            ->greeting('Olá!')
            ->line('Recebemos uma solicitação para redefinir a senha da sua conta.')
            ->action('Redefinir senha', $url)
            ->line("Este link expira em {$minutos} minutos.")
            ->line('Se você não solicitou essa alteração, pode ignorar este e-mail — sua senha continua a mesma.');
    }
}
