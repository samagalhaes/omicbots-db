<?php
// download.php - Manipulador separado para downloads

// Incluir o autoloader do Composer
require 'vendor/autoload.php';

// Incluir arquivos de configuração e funções
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'models/DataModel.php';

// Iniciar sessão para mensagens flash
if (!isset($_SESSION)) {
    session_start();
}

// Verificar se temos um pedido de download válido
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['format'])) {
    // Inicializar o modelo de dados
    $dataModel = new DataModel($conn);
    
    // Obter parâmetros de filtro da URL
    $filters = [
        'categories' => isset($_GET['categories']) ? $_GET['categories'] : [],
        'crop' => isset($_GET['crop']) ? $_GET['crop'] : 'all',
        'project' => isset($_GET['project']) ? $_GET['project'] : 'all',
        'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
        'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
        'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 1000
    ];
    
    // Verificar limite máximo de exportação
    if ($filters['limit'] > MAX_EXPORT_LIMIT || $filters['limit'] == 0) {
        $filters['limit'] = MAX_EXPORT_LIMIT;
        logMessage("Export limit capped at " . MAX_EXPORT_LIMIT, 'info');
    }
    
    // Consultar dados com os filtros
    $result = $dataModel->queryData($filters);
    
    if (isset($result['error'])) {
        // Se houver erro, redirecionar com mensagem de erro
        setFlashMessage("Erro ao exportar dados: " . $result['error'], 'error');
        header("Location: index.php");
        exit;
    }
    
    // Verificar se temos dados para exportar
    if (empty($result['data'])) {
        setFlashMessage("Não foram encontrados dados para exportar com os filtros especificados.", 'warning');
        header("Location: index.php");
        exit;
    }
    
    // Definir nome do arquivo
    $format = strtoupper($_GET['format']);
    $fileTimestamp = date('Y-m-d_His');
    
    // Definir partes do nome do arquivo com base nos filtros
    $filenameParts = ['agricultural_data'];
    
    if (!empty($filters['crop']) && $filters['crop'] != 'all') {
        $filenameParts[] = slugify($filters['crop']);
    }
    
    if (!empty($filters['project']) && $filters['project'] != 'all') {
        $filenameParts[] = slugify($filters['project']);
    }
    
    $filenameParts[] = $fileTimestamp;
    $filenameBase = implode('_', $filenameParts);
    
    // Exportar no formato solicitado
    if ($format == 'CSV') {
        $filename = $filenameBase . '.csv';
        logMessage("Exporting CSV: $filename with " . count($result['data']) . " records", 'info');
        $dataModel->exportToCSV($result['data'], $filename);
    } elseif ($format == 'XLSX') {
        $filename = $filenameBase . '.xlsx';
        logMessage("Exporting XLSX: $filename with " . count($result['data']) . " records", 'info');
        $dataModel->exportToExcel($result['data'], $filename);
    } else {
        // Formato não suportado
        setFlashMessage("Formato de exportação não suportado: $format", 'error');
        header("Location: index.php");
        exit;
    }
} else {
    // Parâmetros inválidos
    setFlashMessage("Parâmetros de download inválidos.", 'error');
    header("Location: index.php");
    exit;
}
?>