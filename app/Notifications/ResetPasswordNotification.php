<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        $message = (new MailMessage())
            ->subject('Restablecer contraseña — ERP Ciete')
            ->greeting('Hola ' . ($notifiable->nombre ?? ''))
            ->line('Recibes este correo porque se ha solicitado restablecer la contraseña de tu cuenta en el ERP de Ciete.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace caducará en ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minutos.')
            ->line('Si no solicitaste el cambio de contraseña, no es necesario realizar ninguna acción.')
            ->salutation('— Equipo ERP Ciete');

        if (! app()->environment('production')) {
            $message->mailer('log');
        }

        return $message;
    }
}
