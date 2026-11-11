<?php

/**
 * logger.php
 *
 * Sistema de logs de auditoria para autenticação e gerenciamento de sessões.
 *
 * Cada linha do arquivo de log é um JSON (formato ndjson) contendo:
 *  - timestamp (ISO-8601, com fuso horário)
 *  - nivel (INFO, WARNING, SECURITY)
 *  - evento (LOGIN_SUCCESS, LOGIN_FAILED, LOGOUT, SESSION_EXPIRED, SUSPICIOUS_SESSION_MUTATION, ...)
 *  - usuario (identificador do usuário, quando disponível)
 *  - ip (endereço IP de origem)
 *  - user_agent (navegador/dispositivo de origem)
 *  - request_id (UUID por requisição, para correlacionar eventos)
 *
 * IMPORTANTE (privacidade / segurança):
 *  - NUNCA registrar senha (nem em texto puro, nem hash) neste log.
 *  - NUNCA registrar o valor completo do ID de sessão (PHPSESSID / app_login).
 *  - O log fica em uma pasta própria com .htaccess bloqueando acesso via navegador.
 */

define('LOG_DIR', __DIR__ . '/logs');
define('LOG_FILE', LOG_DIR . '/auth.log');

/**
 * Garante que a pasta de logs existe e não é acessível diretamente pelo navegador.
 */
function garantir_diretorio_log(): void
{
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0750, true);
    }

    $htaccess = LOG_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        // Bloqueia acesso HTTP direto aos arquivos de log (Apache 2.4+)
        file_put_contents($htaccess, "Require all denied\n");
    }
}

/**
 * Gera (uma única vez por requisição) um UUID v4 para correlacionar
 * todos os eventos de log gerados durante essa mesma requisição.
 */
function obter_request_id(): string
{
    static $request_id = null;

    if ($request_id === null) {
        $dados = random_bytes(16);
        $dados[6] = chr(ord($dados[6]) & 0x0f | 0x40); // versão 4
        $dados[8] = chr(ord($dados[8]) & 0x3f | 0x80); // variante RFC 4122

        $hex = bin2hex($dados);
        $request_id = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    return $request_id;
}

/**
 * Obtém o IP de origem da requisição.
 *
 * Observação de segurança: em produção, só confie em cabeçalhos de proxy
 * reverso (ex.: X-Forwarded-For) se o servidor estiver de fato atrás de um
 * proxy confiável e configurado para sobrescrever esse cabeçalho — caso
 * contrário, o cliente pode falsificá-lo livremente. Em ambiente de
 * desenvolvimento (XAMPP local), usamos apenas REMOTE_ADDR.
 */
function obter_ip_cliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
}

/**
 * Registra uma linha de log de auditoria.
 *
 * @param string $nivel   INFO | WARNING | SECURITY
 * @param string $evento  Ex: LOGIN_SUCCESS, LOGIN_FAILED, LOGOUT, SESSION_EXPIRED, SUSPICIOUS_SESSION_MUTATION
 * @param int|null $usuario_id Identificador do usuário (ex: email), quando disponível
 * @param array $extra   Dados adicionais não sensíveis (ex: motivo da falha)
 */
function registrar_log(string $nivel, string $evento, ?int $usuario_id = null, array $extra = []): void
{
    garantir_diretorio_log();

    $registro = [
        'timestamp'  => date('Y-m-d H:i:s P'), // Ex: 2026-08-19 21:13:00 -03:00 (igual ao formato exigido)
        'nivel'      => $nivel,
        'evento'     => $evento,
        'usuario_id'    => $usuario_id ?? 'anonimo',
        'ip'         => obter_ip_cliente(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido',
        'request_id' => obter_request_id(),
    ];

    if (!empty($extra)) {
        $registro['detalhes'] = $extra;
    }

    $linha = json_encode($registro, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    // LOCK_EX evita que duas requisições simultâneas corrompam o arquivo
    file_put_contents(LOG_FILE, $linha, FILE_APPEND | LOCK_EX);
}