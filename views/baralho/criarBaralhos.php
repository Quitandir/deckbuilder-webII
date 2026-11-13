<!DOCTYPE html>
<html lang="pt-br">
<?php $titulo = 'Novo baralho'; require 'views/partials/head.php'; ?>
<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo max-w-3xl">
        <h1 class="titulo">Novo baralho</h1>

        <?php require 'views/partials/mensagens.php'; ?>

        <form action="index.php?modulo=baralho&acao=criarBaralho" method="POST" class="painel space-y-4">
            <input type="hidden" id="comandanteCartaId" name="comandante_carta_id" value="">

            <div>
                <label for="nome" class="rotulo">Nome</label>
                <input type="text" id="nome" name="nome" class="campo" maxlength="100" required>
            </div>

            <div>
                <label for="descricao" class="rotulo">Descrição</label>
                <textarea id="descricao" name="descricao" rows="3" class="campo" required></textarea>
            </div>

            <div>
                <label for="buscaCarta" class="rotulo">Comandante</label>
                <div class="relative max-w-md">
                    <input type="text" id="buscaCarta" class="campo" placeholder="Digite o nome de uma criatura lendária"
                        autocomplete="off">
                    <ul id="listaSugestoes" class="lista-sugestoes" style="display: none;"></ul>
                </div>
                <p id="erroComandante" role="alert" class="mensagem-erro mt-2" hidden></p>
                <div id="comandante-frame" class="mt-3 w-48"></div>
            </div>

            <fieldset>
                <legend class="rotulo">Arquétipos</legend>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-3">
                    <?php foreach ($arquetipos as $arquetipo): ?>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="arquetipos[]" value="<?= $arquetipo->id ?>">
                            <?= htmlspecialchars($arquetipo->nome) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="botao">Criar baralho</button>
                <a href="index.php?modulo=baralho&acao=telaBaralhos" class="botao-secundario">Cancelar</a>
            </div>
        </form>
    </main>

    <script src="js/autocomplete_comandante.js"></script>
    <?php require 'views/partials/rodape.php'; ?>
</body>
</html>
