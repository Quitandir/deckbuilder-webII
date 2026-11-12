<?php
// Cada view define $titulo antes de incluir este arquivo. Opcionais:
// $descricaoPagina (texto da página) e $paginaPublica (true para aparecer nos buscadores)
$descricaoPagina = $descricaoPagina ?? 'Deckbuilder de MTG para aula de Web II';
$paginaPublica = $paginaPublica ?? false;

// Endereço completo da página atual, montado com a URL_BASE do config.php
$parametrosUrl = $_SERVER['QUERY_STRING'] ?? '';
$urlPagina = URL_BASE . '/index.php' . ($parametrosUrl !== '' ? '?' . $parametrosUrl : '');
$urlImagem = URL_BASE . '/img/og-imagem.png';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo) ?> | MTG Deck Builder</title>

    <!-- SEO -->
    <meta name="description" content="<?= htmlspecialchars($descricaoPagina) ?>">
    <meta name="robots" content="<?= $paginaPublica ? 'index, follow' : 'noindex, nofollow' ?>">
    <link rel="canonical" href="<?= htmlspecialchars($urlPagina) ?>">

    <!-- Autoria e direitos -->
    <meta name="author" content="Moisés Enick Carniel">
    <meta name="copyright" content="© 2026 MTG Deck Builder. Proibida a reprodução sem autorização.">
    <meta name="dcterms.rights" content="© 2026 MTG Deck Builder. Proibida a reprodução sem autorização.">

    <!-- Open Graph: prévia ao compartilhar o link -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MTG Deck Builder">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="<?= htmlspecialchars($titulo) ?> | MTG Deck Builder">
    <meta property="og:description" content="<?= htmlspecialchars($descricaoPagina) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($urlPagina) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($urlImagem) ?>">

    <?php if ($paginaPublica): ?>
        <!-- GEO: descrição estruturada do site (schema.org) para buscadores e IAs -->
        <script type="application/ld+json">
            <?= json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebApplication',
                'name' => 'MTG Deck Builder',
                'description' => 'Deckbuilder de MTG para aula de Web II',
                'url' => URL_BASE,
                'applicationCategory' => 'GameApplication',
                'operatingSystem' => 'Web',
                'inLanguage' => 'pt-BR',
                'author' => ['@type' => 'Person', 'name' => 'Moisés Enick Carniel'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>
        </script>
    <?php endif; ?>

    <link rel="stylesheet" href="assets/app.css">
</head>
