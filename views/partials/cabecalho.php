<?php // Os links de navegação só aparecem para quem está logado ?>
<header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-2 px-4 py-3">
        <?php $logado = !empty($_SESSION['logado']); ?>
        <a href="<?= $logado ? 'index.php?modulo=baralho&acao=telaBaralhos' : 'index.php' ?>"
            class="text-lg font-bold text-blue-800">MTG Deck Builder</a>

        <?php if ($logado): ?>
            <nav class="flex flex-wrap gap-4">
                <a href="index.php?modulo=baralho&acao=telaBaralhos" class="link">Meus baralhos</a>
                <a href="index.php?modulo=baralho&acao=telaCriarBaralho" class="link">Novo baralho</a>
                <a href="index.php?modulo=usuario&acao=logout" class="link">Sair</a>
            </nav>
        <?php endif; ?>
    </div>
</header>
