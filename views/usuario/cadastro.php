<!DOCTYPE html>
<html lang="pt-br">
<?php
$titulo = 'Cadastro';
$descricaoPagina = 'Página de cadastro do Deckbuilder de MTG da aula de Web II.';
$paginaPublica = true;
require 'views/partials/head.php';
?>

<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-sm">
        <h1 class="titulo">Criar conta</h1>

        <?php require 'views/partials/mensagens.php'; ?>

        <form action="index.php?modulo=usuario&acao=cadastrar" method="POST" class="painel space-y-4">
            <div>
                <label for="nome" class="rotulo">Nome</label>
                <input type="text" id="nome" name="nome" class="campo" autocomplete="name" required>
            </div>
            <div>
                <label for="email" class="rotulo">E-mail</label>
                <input type="email" id="email" name="email" class="campo" autocomplete="email" required>
            </div>
            <div>
                <label for="senha" class="rotulo">Senha</label>
                <input type="password" id="senha" name="senha" class="campo" autocomplete="new-password" minlength="8"
                    required>
                <p class="mt-1 text-sm text-gray-600">No mínimo 8 caracteres.</p>
            </div>
            <div>
                <label for="confirma_senha" class="rotulo">Confirmar senha</label>
                <input type="password" id="confirma_senha" name="confirma_senha" class="campo"
                    autocomplete="new-password" minlength="8" required>
            </div>
            <button type="submit" class="botao w-full">Cadastrar</button>
        </form>

        <p class="mt-4">
            <a href="index.php?modulo=usuario&acao=telaLogin" class="link">Já tenho conta</a>
        </p>
    </main>
</body>

</html>