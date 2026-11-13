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
            <div class="flex items-start gap-2">
                <input type="checkbox" id="aceite_termos" name="aceite_termos" value="1" class="mt-1" required>
                <label for="aceite_termos" class="text-sm">
                    Li e aceito os
                    <a href="index.php?modulo=pagina&acao=telaTermos" target="_blank" rel="noopener" class="link">termos
                        de uso, a política de privacidade e de direitos autorais</a>.
                </label>
            </div>
            <button type="submit" class="botao w-full">Cadastrar</button>
        </form>

        <p class="mt-4">
            <a href="index.php?modulo=usuario&acao=telaLogin" class="link">Já tenho conta</a>
        </p>
    </main>
    <?php require 'views/partials/rodape.php'; ?>
</body>

</html>
