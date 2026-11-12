<!DOCTYPE html>
<html lang="pt-br">
<?php
$titulo = 'Redefinição de senha';
$descricaoPagina = 'Página de redefinição de senha do Deckbuilder de MTG da aula de Web II.';
// Abre com um token de recuperação na URL, então não deve ser indexada
$paginaPublica = false;
require 'views/partials/head.php';
?>
<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-sm">
        <h1 class="titulo">Criar nova senha</h1>

        <?php require 'views/partials/mensagens.php'; ?>

        <form action="index.php?modulo=usuario&acao=salvarNovaSenha" method="POST" class="painel space-y-4">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div>
                <label for="senha" class="rotulo">Nova senha</label>
                <input type="password" id="senha" name="senha" class="campo" autocomplete="new-password" minlength="8"
                    required>
                <p class="mt-1 text-sm text-gray-600">No mínimo 8 caracteres.</p>
            </div>
            <div>
                <label for="confirma_senha" class="rotulo">Confirmar nova senha</label>
                <input type="password" id="confirma_senha" name="confirma_senha" class="campo"
                    autocomplete="new-password" minlength="8" required>
            </div>
            <button type="submit" class="botao w-full">Salvar nova senha</button>
        </form>

        <p class="mt-4">
            <a href="index.php?modulo=usuario&acao=telaLogin" class="link">Voltar para o login</a>
        </p>
    </main>
</body>
</html>
