<?php

namespace App\Service;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SendMailService
{
    private string $username;
    private string $password;
    public function __construct(ParameterBagInterface $params)
    {
        $this->username = $params->get('mail_username');
        $this->password = $params->get('mail_password');
    }

    public function sendMail(string $email, string $code): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'smtp.mail.ru';
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom($this->username, 'CodeTrek');
            $mail->addAddress($email);
            $mail->Subject = 'Код подтверждения';

            $mail->isHTML(true);
            $mail->Body = "
                <html>
                <body style=\"font-family: Arial, sans-serif; background-color: #111111; color: #EEEEEE; padding: 20px;\">
                    <p>Ваш код подтверждения для регистрации:</p>
                    <div style=\"font-weight: bold; font-size: 28px; color: #0d0d0d;\">{$code}</div>
                </body>
                </html>
            ";
            $mail->send();
        } catch (Exception $e) {
            throw new \RuntimeException('Ошибка при отправке письма: ' . $mail->ErrorInfo);
        }
    }
}

