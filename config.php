<?php

// Credenciais e endereço de cada ambiente (XAMPP ou hospedagem)
require_once __DIR__ . '/config_local.php';

date_default_timezone_set('America/Sao_Paulo');

try {
    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_password
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Falha ao conectar no banco: ' . $e->getMessage());
    http_response_code(503);
    die("O sistema está temporariamente indisponível. Tente novamente daqui alguns minutos.");
}