<?php
// Start output buffering
ob_start();

// Initialize session
session_start();


require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'classes/Database.php';
#require_once 'classes/User.php';

// Configurações de upload grandes
ini_set('upload_max_filesize', '3G');
ini_set('post_max_size', '3G');
ini_set('max_execution_time', '3600');
ini_set('max_input_time', '3600');
ini_set('memory_limit', '4G');

// Definir limites de upload
define('MAX_FILE_SIZE', 3 * 1024 * 1024 * 1024); // 3 GB
define('CHUNK_SIZE', 10 * 1024 * 1024); // 10 MB por chunk
define('UPLOAD_DIR', __DIR__ . '/uploads/sql/');

// Garantir diretório de uploads
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}



// Função para proteção de rota de admin
function requireAdmin()
{
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        error_log("Tentativa não autorizada de upload SQL por: " .
            ($_SESSION['username'] ?? 'usuário não identificado'));

        $_SESSION['error_message'] = 'Acesso restrito. Apenas administradores podem fazer upload de SQL.';

        header('Location: index.php');
        exit();
    }
}

// Lidar com upload em partes
function handleLargeFileUpload($user_info)
{
    // Validações iniciais
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        return [
            'success' => false,
            'error' => 'Erro no upload do arquivo'
        ];
    }

    $file = $_FILES['sql_file'];
    $original_filename = $file['name'];

    // Sanitizar nome do arquivo
    $safe_filename = preg_replace('/[^a-zA-Z0-9_.-]/', '', $original_filename);
    $unique_prefix = uniqid('sql_');
    $upload_path = $GLOBALS['upload_dir'] . $unique_prefix . '_' . $safe_filename;

    // Validações de segurança
    try {
        // Verificar tipo de arquivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mime_types = [
            'text/plain',
            'text/x-sql',
            'application/sql',
            'application/octet-stream'
        ];

        if (!in_array($mime_type, $allowed_mime_types)) {
            throw new Exception('Tipo de arquivo não permitido: ' . $mime_type);
        }

        // Verificar tamanho do arquivo
        if ($file['size'] > $GLOBALS['upload_max_size']) {
            throw new Exception('Arquivo muito grande. Limite máximo: ' .
                ($GLOBALS['upload_max_size'] / 1024 / 1024) . ' MB');
        }

        // Mover arquivo uploaded
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Falha ao mover arquivo uploaded');
        }

        // Validar conteúdo do arquivo SQL
        validateSQLFile($upload_path);

        // Registrar log de upload
        $db = new Database();
        $conn = $db->getConnection();

        $log_query = "INSERT INTO file_upload_logs 
                      (filename, total_size, mime_type, upload_status, user_id, user_username, ip_address)
                      VALUES 
                      (:filename, :total_size, :mime_type, :upload_status, :user_id, :username, :ip_address)";

        $log_stmt = $conn->prepare($log_query);
        $log_stmt->execute([
            ':filename' => $safe_filename,
            ':total_size' => $file['size'],
            ':mime_type' => $mime_type,
            ':upload_status' => 'completed',
            ':user_id' => $user_info['id'],
            ':username' => $user_info['username'],
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        // Retornar caminho do arquivo
        return [
            'success' => true,
            'filepath' => $upload_path,
            'filename' => $safe_filename,
            'filesize' => $file['size']
        ];
    } catch (Exception $e) {
        // Log de erro
        error_log("Erro no upload SQL: " . $e->getMessage());

        // Remover arquivo em caso de erro
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Função de validação de arquivo SQL
function validateSQLFile($filepath)
{
    // Verificar extensão
    $allowed_extensions = ['sql'];
    $file_extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('Extensão de arquivo não permitida');
    }

    // Verificar tamanho máximo
    if (filesize($filepath) > $GLOBALS['upload_max_size']) {
        throw new Exception('Arquivo excede o tamanho máximo permitido');
    }

    // Verificar comandos proibidos
    $forbidden_commands = [
        'DROP DATABASE',
        'TRUNCATE TABLE',
        'DELETE FROM',
        '--',
        '/*',
        '*/'
    ];

    // Abrir arquivo e verificar conteúdo
    $file = fopen($filepath, 'r');
    $line_count = 0;
    $max_lines_to_check = 1000; // Limitar número de linhas verificadas

    while (($line = fgets($file)) !== false && $line_count < $max_lines_to_check) {
        $line = strtoupper(trim($line));

        // Verificar comandos proibidos
        foreach ($forbidden_commands as $command) {
            if (strpos($line, strtoupper($command)) !== false) {
                fclose($file);
                throw new Exception("Comando potencialmente perigoso encontrado: $command");
            }
        }

        $line_count++;
    }

    fclose($file);
    return true;
}

// Chamar função de proteção
//requireAdmin();

// Configurações do usuário atual
$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
];

// Initialize Smarty
$smarty = new Smarty();
$smarty->setTemplateDir('templates');
$smarty->setCompileDir('templates_c');
$smarty->setCacheDir('cache');
$smarty->setConfigDir('configs');

// Processamento de upload em partes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar ação
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_chunk') {
        // Upload de chunk
        header('Content-Type: application/json');
        echo handleLargeFileUpload($current_user);
        exit();
    }
}

// Inicializar variáveis
$error = '';
$success = '';

// Atribuir variáveis para o template
$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('max_file_size', MAX_FILE_SIZE / 1024 / 1024);
$smarty->assign('current_user', $current_user);

// Exibir template
$smarty->display('upload-sql.tpl');

// Finalizar buffer de saída
ob_end_flush();
