<?php
session_start();

require_once "config.php";
require_once "vendor/autoload.php";

// Captura o módulo e a ação (padrão: usuario / telaLogin)
$modulo = $_GET['modulo'] ?? 'usuario';
$acao   = $_GET['acao']   ?? 'telaLogin';

// Define o nome da classe do Controller e o caminho do arquivo
$controllerNome = ucfirst($modulo) . "Controller"; // Ex: UsuarioController
$arquivoController = "controllers/{$controllerNome}.php";

// Verifica se o controller existe
if (file_exists($arquivoController)) {
    require_once $arquivoController;
    $controller = new $controllerNome($db);

    // Verifica se a ação (método) existe dentro da classe
    if (method_exists($controller, $acao)) {
        $controller->$acao();
    } else {
        echo "Ação não encontrada!";
    }
} else {
    echo "Página não encontrada!";
}