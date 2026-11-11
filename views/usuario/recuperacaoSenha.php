<!DOCTYPE html>
<html lang="pt-br">
<?php $titulo = 'Recuperação de senha'; require 'views/partials/head.php'; ?>
<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-sm">
        <h1 class="titulo">Recuperar senha</h1>

        <?php require 'views/partials/mensagens.php'; ?>

        <form action="index.php?modulo=usuario&acao=recuperarSenha" method="POST" class="painel space-y-4">
            <p class="text-gray-700">Informe seu e-mail. Se ele estiver cadastrado, você receberá um link para criar
                uma nova senha.</p>
            <div>
                <label for="email" class="rotulo">E-mail</label>
                <input type="email" id="email" name="email" class="campo" autocomplete="email" required>
            </div>
            <button type="submit" class="botao w-full">Enviar link</button>
        </form>

        <p class="mt-4">
            <a href="index.php?modulo=usuario&acao=telaLogin" class="link">Voltar para o login</a>
        </p>
    </main>
</body>
</html>
