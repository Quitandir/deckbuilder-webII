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

    // Verifica se a ação existe e é pública, antes de criar o controller.
    // Chamado daqui de fora, get_class_methods lista só os métodos públicos;
    // os que começam com __ (como __construct) são internos do PHP, não ações.
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
