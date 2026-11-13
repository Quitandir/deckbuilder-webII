<!DOCTYPE html>
<html lang="pt-br">
<?php $titulo = $baralho->nome; require 'views/partials/head.php'; ?>
<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo">
        <div class="mb-4">
            <h1 class="titulo mb-1 break-words"><?= htmlspecialchars($baralho->nome) ?></h1>
            <?php if (!empty($baralho->descricao)): ?>
                <p class="text-gray-700"><?= htmlspecialchars($baralho->descricao) ?></p>
            <?php endif; ?>
        </div>

        <?php require 'views/partials/mensagens.php'; ?>

        <?php // <details> deixa o formulário recolhido; ele abre sozinho depois de um erro ?>
        <details class="painel mb-6" <?= !empty($erro) ? 'open' : '' ?>>
            <summary class="cursor-pointer font-semibold">Editar nome, descrição e arquétipos</summary>

            <form action="index.php?modulo=baralho&acao=atualizarBaralho" method="POST" class="mt-4 max-w-2xl space-y-4">
                <input type="hidden" name="id" value="<?= $baralho->id ?>">

                <div>
                    <label for="nome" class="rotulo">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($baralho->nome) ?>" class="campo"
                        maxlength="100" required>
                </div>

                <div>
                    <label for="descricao" class="rotulo">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3"
                        class="campo"><?= htmlspecialchars($baralho->descricao ?? '') ?></textarea>
                </div>

                <fieldset>
                    <legend class="rotulo">Arquétipos</legend>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-3">
                        <?php foreach ($todosArquetipos as $arquetipo): ?>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="arquetipos[]" value="<?= $arquetipo->id ?>"
                                    <?= in_array($arquetipo->id, $idsArquetiposDoBaralho) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($arquetipo->nome) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <button type="submit" class="botao">Salvar alterações</button>
            </form>
        </details>

        <div id="editor-baralho"
            data-baralho-id="<?= $baralho->id ?>"
            data-comandante-id="<?= $baralho->comandante_carta_id ?>">
        </div>
    </main>
  <?php require 'views/partials/rodape.php'; ?>                          
    <script type="module" src="assets/app.js"></script>
</body>
</html>
