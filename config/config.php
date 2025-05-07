<?php
// config/config.php - Configuração da aplicação

// Configurações do Banco de Dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'omicbotsuser');
define('DB_PASSWORD', 'omicbots#');
define('DB_NAME', 'omicbotsdb');

// Estabelecer conexão com o banco de dados
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar conexão
if ($conn === false) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Definir cabeçalhos UTF-8
header('Content-Type: text/html; charset=utf-8');

// Configurações da aplicação
define('APP_NAME', 'OMICSTAT');
define('APP_VERSION', '1.0.0');
define('MAX_EXPORT_LIMIT', 10000); // Limite máximo para exportações

// Configurações do Smarty (adicionais)
define('SMARTY_DEBUG', false);      // Ativar/desativar debug do Smarty
define('TEMPLATE_CACHING', false);  // Ativar/desativar cache de templates
define('CACHE_LIFETIME', 3600);     // Tempo de vida do cache em segundos
?>