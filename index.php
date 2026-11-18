<?php
session_start();

require_once "config.php";
require_once "vendor/autoload.php";
require_once 'utils/logger.php';

$modulo = $_GET['modulo'] ?? 'usuario';
$acao   = $_GET['acao']   ?? 'telaLogin';

$controllerNome = ucfirst($modulo) . "Controller"; // Ex: UsuarioController
$arquivoController = "controllers/{$controllerNome}.php";

if (file_exists($arquivoController)) {
    require_once $arquivoController;

    // Verifica se a ação existe no cotrolador e se ela é pública.
    // Construtor começando com __ fica de fora
    if (in_array($acao, get_class_methods($controllerNome)) && !str_starts_with($acao, '__')) {
        // Registra antes do construtor, que pode redirecionar quem não está logado
        registrar_log('INFO', 'ROUTE', $_SESSION['usuario_id'] ?? null, ['modulo' => $modulo, 'acao' => $acao]);

        $controller = new $controllerNome($db);
        $controller->$acao();
    } else {
        registrar_log('WARNING', 'ACTION_NOT_FOUND', $_SESSION['usuario_id'] ?? null, ['modulo' => $modulo, 'acao' => $acao]);
        echo "Ação não encontrada!";
    }
} else {
    registrar_log('WARNING', 'PAGE_NOT_FOUND', $_SESSION['usuario_id'] ?? null, ['modulo' => $modulo, 'acao' => $acao]);
    echo "Página não encontrada!";
}
