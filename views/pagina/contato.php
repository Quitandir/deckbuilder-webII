<!DOCTYPE html>
<html lang="pt-br">
<?php
$titulo = 'Fale conosco';
$descricaoPagina = 'Entre em contato com o MTG Deck Builder: dúvidas, sugestões e pedidos sobre seus dados pessoais.';
$paginaPublica = true;
require 'views/partials/head.php';

// Valores enviados antes, para não apagar o que a pessoa digitou quando der erro
$valores = $valores ?? [];
?>

<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-xl">
        <h1 class="titulo">Fale conosco</h1>
        <p class="mb-4 text-gray-700">Tem uma dúvida, sugestão ou pedido sobre os seus dados? Envie uma mensagem.</p>

        <?php require 'views/partials/mensagens.php'; ?>

        <form action="index.php?modulo=pagina&acao=enviarContato" method="POST" class="painel space-y-4">
            <div>
                <label for="nome" class="rotulo">Nome</label>
                <input type="text" id="nome" name="nome" class="campo" autocomplete="name" maxlength="100" required
                    value="<?= htmlspecialchars($valores['nome'] ?? '') ?>">
            </div>

            <div>
                <label for="email" class="rotulo">E-mail</label>
                <input type="email" id="email" name="email" class="campo" autocomplete="email" maxlength="150" required
                    value="<?= htmlspecialchars($valores['email'] ?? '') ?>">
                <p class="mt-1 text-sm text-gray-600">Usado apenas para responder a sua mensagem.</p>
            </div>

            <div>
                <label for="assunto" class="rotulo">Assunto</label>
                <input type="text" id="assunto" name="assunto" class="campo" maxlength="150" required
                    value="<?= htmlspecialchars($valores['assunto'] ?? '') ?>">
            </div>

            <div>
                <label for="mensagem" class="rotulo">Mensagem</label>
                <textarea id="mensagem" name="mensagem" rows="6" class="campo" minlength="10" maxlength="5000"
                    required><?= htmlspecialchars($valores['mensagem'] ?? '') ?></textarea>
                <p class="mt-1 text-sm text-gray-600">Entre 10 e 5000 caracteres.</p>
            </div>

            <p class="text-sm text-gray-600">
                Ao enviar, você concorda com o uso dos seus dados para responder a esta mensagem, conforme os
                <a href="index.php?modulo=pagina&acao=telaTermos" class="link">termos de uso e a política de privacidade</a>.
            </p>

            <button type="submit" class="botao w-full">Enviar mensagem</button>
        </form>
    </main>

    <?php require 'views/partials/rodape.php'; ?>
</body>
</html>
