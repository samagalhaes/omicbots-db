<?php
// includes/functions.php - Funções auxiliares para a aplicação

/**
 * Função para formatar valores para exibição
 */
function formatValue($value) {
    if (is_numeric($value)) {
        return number_format($value, 2, ',', '.');
    }
    return htmlspecialchars($value);
}

/**
 * Função para validar datas
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Função para sanitizar strings para uso em SQL
 */
function sanitizeString($conn, $string) {
    return mysqli_real_escape_string($conn, trim($string));
}

/**
 * Função para criar arrays de seleção para os templates
 */
function createSelectOptions($items, $valueField = null, $labelField = null) {
    $options = [];
    
    foreach ($items as $item) {
        if (is_array($item) && $valueField !== null && $labelField !== null) {
            $options[] = [
                'value' => $item[$valueField],
                'label' => $item[$labelField]
            ];
        } else {
            $options[] = [
                'value' => $item,
                'label' => $item
            ];
        }
    }
    
    return $options;
}

/**
 * Função para gerar mensagens de log
 */
function logMessage($message, $level = 'info') {
    $logFile = 'logs/application.log';
    $date = date('Y-m-d H:i:s');
    $formattedMessage = "[$date] [$level]: $message" . PHP_EOL;
    
    // Verificar se o diretório existe, se não, criar
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    // Adicionar a mensagem ao arquivo de log
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
}

/**
 * Função para verificar se uma categoria de dados está selecionada
 */
function isCategorySelected($category, $selectedCategories) {
    return in_array($category, $selectedCategories);
}

/**
 * Função para gerar URLs amigáveis
 */
function slugify($text) {
    // Substituir caracteres não alfanuméricos por hífen
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Transliterar
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Remover caracteres indesejados
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Converter para minúsculo
    $text = strtolower($text);
    
    // Remover hífens duplicados
    $text = preg_replace('~-+~', '-', $text);
    
    // Remover hífens do início e do fim
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Função para gerar URLs para download
 */
function generateDownloadUrl($params) {
    $url = 'download.php?';
    $queryParams = [];
    
    foreach ($params as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $item) {
                $queryParams[] = urlencode($key) . '[]=' . urlencode($item);
            }
        } else {
            $queryParams[] = urlencode($key) . '=' . urlencode($value);
        }
    }
    
    return $url . implode('&', $queryParams);
}

/**
 * Função para definir mensagens flash para o usuário
 */
function setFlashMessage($message, $type = 'success') {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Função para obter e limpar mensagens flash
 */
function getFlashMessage() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    $message = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    
    return $message;
}
?>