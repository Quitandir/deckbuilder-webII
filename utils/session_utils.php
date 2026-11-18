<?php

// Arquivo com funções para validar e proteger as sessões geradas pelo sistema

require_once __DIR__ . '/logger.php';

// Tempo máximo de inatividade antes da sessão expirar automaticamente (em segundos)
define('SESSAO_TIMEOUT_SEGUNDOS', 900); // 15 minutos

// Registra uma impressão digital da sessão
function registrar_fingerprint_sessao(): void
{
    $_SESSION['ip_fingerprint'] = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
    $_SESSION['ua_fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $_SESSION['last_activity']  = time();
}

// Verificação de sessão ativa, se é o usuário certo e outras mutações de sessão
function verificar_sessao_ativa(): bool
{
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        return false;
    }

    $usuario_id = $_SESSION['usuario_id'] ?? null;

    // 1. Expiração por inatividade
    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > SESSAO_TIMEOUT_SEGUNDOS) {
        registrar_log('INFO', 'SESSION_EXPIRED', $usuario_id);
        finalizar_sessao();
        return false;
    }

    // 2. Mutação suspeita de IP
    $ip_atual = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
    if (isset($_SESSION['ip_fingerprint']) && $_SESSION['ip_fingerprint'] !== $ip_atual) {
        registrar_log('SECURITY', 'SUSPICIOUS_SESSION_MUTATION', $usuario_id, [
            'motivo' => 'IP de origem mudou durante a sessão',
        ]);
        finalizar_sessao();
        return false;
    }

    // 3. Mutação suspeita de User-Agent
    $ua_atual = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (isset($_SESSION['ua_fingerprint']) && $_SESSION['ua_fingerprint'] !== $ua_atual) {
        registrar_log('SECURITY', 'SUSPICIOUS_SESSION_MUTATION', $usuario_id, [
            'motivo' => 'User-Agent mudou durante a sessão',
        ]);
        finalizar_sessao();
        return false;
    }

    // Sessão válida: atualiza o "último acesso" para renovar o timeout de inatividade
    $_SESSION['last_activity'] = time();

    return true;
}

// Encerra a sessão de forma completa e segura: limpa os dados da sessão,apaga o cookie de sessão e destrói a sessão no servidor.
function finalizar_sessao(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();
}