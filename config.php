<?php

$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'deck_builder_mtg';
$port = '3306';

date_default_timezone_set('America/Sao_Paulo');

define('URL_BASE', 'http://localhost/web_ii/projeto_final');

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