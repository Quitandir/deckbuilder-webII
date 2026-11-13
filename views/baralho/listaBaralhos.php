<!DOCTYPE html>
<html lang="pt-br">
<?php $titulo = 'Meus baralhos'; require 'views/partials/head.php'; ?>
<body>
    <?php require 'views/partials/cabecalho.php'; ?>

    <main class="conteudo">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h1 class="titulo mb-0">Meus baralhos</h1>
            <a href="index.php?modulo=baralho&acao=telaCriarBaralho" class="botao">Novo baralho</a>
        </div>

        <?php require 'views/partials/mensagens.php'; ?>

        <?php if (empty($baralhosDoUsuario)): ?>
            <div class="painel text-center">
                <p class="mb-4 text-gray-700">Você ainda não criou nenhum baralho.</p>
                <a href="index.php?modulo=baralho&acao=telaCriarBaralho" class="botao">Criar meu primeiro baralho</a>
            </div>
        <?php else: ?>
            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($baralhosDoUsuario as $baralho): ?>
                    <li class="painel flex gap-4">
                        <?php if ($baralho->comandante_imagem): ?>
                            <img src="<?= htmlspecialchars($baralho->comandante_imagem) ?>"
                                alt="<?= htmlspecialchars($baralho->comandante_nome) ?>" class="w-24 shrink-0 self-start rounded-md">
                        <?php endif; ?>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <h2 class="text-lg font-semibold break-words"><?= htmlspecialchars($baralho->nome) ?></h2>
                            <?php if ($baralho->comandante_nome): ?>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($baralho->comandante_nome) ?></p>
                            <?php endif; ?>
                            <p class="text-sm text-gray-600"><?= (int) $baralho->total_cartas ?>/100 cartas</p>

                            <div class="mt-auto flex flex-wrap gap-2 pt-3">
                                <a href="index.php?modulo=baralho&acao=telaEditarBaralho&id=<?= $baralho->id ?>"
                                    class="botao px-3 py-1.5 text-sm">Editar</a>
                                <form action="index.php?modulo=baralho&acao=deletarBaralho" method="POST">
                                    <input type="hidden" name="id" value="<?= $baralho->id ?>">
                                    <button type="submit" class="botao-perigo px-3 py-1.5 text-sm"
                                        onclick="return confirm('Tem certeza que deseja excluir este baralho?');">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
    <?php require 'views/partials/rodape.php'; ?>
</body>
</html>
