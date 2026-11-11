<?php
    // Copie este arquivo como phpmailer_config.php e preencha com uma conta de e-mail.
    // Com Gmail: ative a verificação em duas etapas e gere uma "senha de app" em
    // https://myaccount.google.com/apppasswords. Use essa senha, não a senha da conta.
    // Sem configuração, a recuperação de senha não envia o e-mail.
    $servidor_mailer = 'smtp.gmail.com';
    $porta_mailer = 587; //STARTTLS
    $usuario_mailer = 'seu-email@gmail.com';
    $senha_mailer = 'senha-de-app-de-16-caracteres';
