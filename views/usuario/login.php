<!DOCTYPE html>
<html lang="pt-br">
<?php
$titulo = 'Login';
$descricaoPagina = 'Página de login do Deckbuilder de MTG da aula de Web II.';
$paginaPublica = true;
require 'views/partials/head.php';
?>

<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-sm">
        <h1 class="titulo">Entrar</h1>

        <?php require 'views/partials/mensagens.php'; ?>

        <form action="index.php?modulo=usuario&acao=login" method="POST" class="painel space-y-4">
            <div>
                <label for="email" class="rotulo">E-mail</label>
                <input type="email" id="email" name="email" class="campo" autocomplete="email" required>
            </div>
            <div>
                <label for="senha" class="rotulo">Senha</label>
                <input type="password" id="senha" name="senha" class="campo" autocomplete="current-password" required>
            </div>
            <button type="submit" class="botao w-full">Entrar</button>
        </form>

        <p class="mt-4 flex flex-wrap justify-between gap-2">
            <a href="index.php?modulo=usuario&acao=telaCadastro" class="link">Criar conta</a>
            <a href="index.php?modulo=usuario&acao=telaRecuperacao" class="link">Esqueceu sua senha?</a>
        </p>
    </main>
    <?php require 'views/partials/rodape.php'; ?>
</body>

</html>
