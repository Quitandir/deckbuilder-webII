<?php

// Arquivo utilitário com as funções de log do sistema

define('LOG_DIR', __DIR__ . '/logs');
define('LOG_FILE', LOG_DIR . '/auth.log');


// Proteção para logs não serem acessados pelo navegador

function garantir_diretorio_log(): void
{
    // 0755 deixa o usuário de FTP ler a pasta, mesmo quando o PHP roda com outro usuário.
    // O acesso pelo navegador continua bloqueado pelo .htaccess abaixo.
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0755, true);
    } elseif ((fileperms(LOG_DIR) & 0777) !== 0755) {
        // Corrige a pasta criada antes com 0750
        @chmod(LOG_DIR, 0755);
    }

    if (file_exists(LOG_FILE) && (fileperms(LOG_FILE) & 0777) !== 0644) {
        @chmod(LOG_FILE, 0644);
    }

    $htaccess = LOG_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        // Bloqueia acesso HTTP direto aos arquivos de log (Apache 2.4+)
        file_put_contents($htaccess, "Require all denied\n");
    }
}


// Gera (uma única vez por requisição) um UUID v4 para correlacionar todos os eventos de log gerados durante essa mesma requisição.

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

// Obtem IP do cliente
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