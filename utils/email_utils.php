<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviar_email_recuperacao_senha($destinatario, $link)
{

    require "phpmailer_config.php";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                                //Send using SMTP
        $mail->Host = $servidor_mailer;               //Set the SMTP server to send through
        $mail->SMTPAuth = true;                         //Enable SMTP authentication
        $mail->Username = $usuario_mailer;            //SMTP username
        $mail->Password = $senha_mailer;                     //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
        $mail->Port = $porta_mailer;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($usuario_mailer, 'Deckbuilder Web II');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Recuperação de senha do Deck Builder';
        $mail->Body = 
        '<h4>Recupere sua senha através desse <a href="' . $link . '">link!</a></h4>';
        $mail->AltBody = 'Recupere sua senha através do link: ' . $link;

        $mail->send();
        return true;
    } catch (Exception $e) {

        registrar_log("WARNING", "EMAIL_FAILED", NULL , ['motivo' => $mail->ErrorInfo]);
        return false;
    }

}