<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class PortalAccessInviteNotification extends BaseResetPasswordNotification
{
    public function __construct(
        #[\SensitiveParameter] $token,
        private readonly string $nomeEmpresa,
    ) {
        parent::__construct($token);
    }

    protected function buildMailMessage($url)
    {
        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $dias = intdiv((int) $minutos, 1440);

        return (new MailMessage)
            ->subject('Seu acesso ao portal foi criado — '.config('app.name'))
            ->greeting('Olá!')
            ->line("Um acesso ao portal do cliente foi criado para você em nome de {$this->nomeEmpresa}.")
            ->line('Para começar a usar, clique no botão abaixo e defina sua senha.')
            ->action('Definir minha senha', $url)
            ->line("Este link expira em {$dias} dias.")
            ->line('Se você não esperava este e-mail, pode ignorá-lo.');
    }
}
