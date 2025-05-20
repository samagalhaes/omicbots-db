<?php
// Start output buffering
ob_start();

// Initialize session
session_start();

require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

// Função para proteção de rota de admin
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        error_log("Tentativa não autorizada de execução SQL por: " . 
                   ($_SESSION['username'] ?? 'usuário não identificado'));
        
        $_SESSION['error_message'] = 'Acesso restrito. Apenas administradores podem executar SQL.';
        
        header('Location: index.php');
        exit();
    }
}

// Chamar função de proteção
requireAdmin();

// Initialize Smarty
$smarty = new Smarty();
$smarty->setTemplateDir('templates');
$smarty->setCompileDir('templates_c');
$smarty->setCacheDir('cache');
$smarty->setConfigDir('configs');

// Initialize Database
$db = new Database();
$conn = $db->getConnection();

// Usuário atual
$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
];

// Variáveis para resultados e erros
$sql_result = null;
$sql_error = '';
$execution_time = 0;
$affected_rows = 0;

// Processar execução de SQL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_query'])) {
    try {
        // Iniciar cronômetro
        $start_time = microtime(true);

        // Limpar e validar entrada
        $sql_query = trim($_POST['sql_query']);

        // Lista de comandos proibidos
        $forbidden_commands = [
            // 'DROP DATABASE',
            // 'TRUNCATE TABLE',
            // 'DELETE FROM',
            // 'INSERT INTO',
            // 'UPDATE ',
            // 'CREATE DATABASE',
            // 'ALTER DATABASE',
            // 'CREATE TABLE',
            // 'ALTER TABLE',
            // 'DROP TABLE'
        ];

        // Verificar comandos proibidos
        $upper_query = strtoupper($sql_query);
        foreach ($forbidden_commands as $forbidden) {
            if (strpos($upper_query, $forbidden) !== false) {
                throw new Exception("Comando SQL não permitido: $forbidden");
            }
        }

        // Preparar e executar consulta
        $stmt = $conn->prepare($sql_query);
        $stmt->execute();

        // Calcular tempo de execução
        $end_time = microtime(true);
        $execution_time = round($end_time - $start_time, 4);

        // Obter número de linhas afetadas
        $affected_rows = $stmt->rowCount();

        
        // Verificar se é uma consulta SELECT
        //if (stripos($upper_query, 'SELECT') === 0) {
            // Buscar resultados
            $sql_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //}
        print_r($sql_result);

    } catch (PDOException $e) {
        // Registrar log de erro
        $log_query = "INSERT INTO sql_query_logs 
                      (user_id, username, query, execution_time, affected_rows, query_type, status, error_message)
                      VALUES 
                      (:user_id, :username, :query, :execution_time, 0, 'ERROR', 'failed', :error_message)";
        
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->execute([
            ':user_id' => $current_user['id'],
            ':username' => $current_user['username'],
            ':query' => $sql_query,
            ':execution_time' => 0,
            ':error_message' => $e->getMessage()
        ]);

        // Definir erro para exibição
        $sql_error = $e->getMessage();
    } catch (Exception $e) {
        // Erros de validação
        $sql_error = $e->getMessage();
    }
}

// Buscar lista de bancos de dados
try {
    $databases_stmt = $conn->query("SHOW DATABASES");
    $databases = $databases_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $databases = [];
    $sql_error .= "\nFalha ao listar bancos de dados: " . $e->getMessage();
}

// Atribuir variáveis para o template
$smarty->assign('current_user', $current_user);
$smarty->assign('databases', $databases);
$smarty->assign('sql_result', $sql_result);
$smarty->assign('sql_error', $sql_error);
$smarty->assign('execution_time', $execution_time);
$smarty->assign('affected_rows', $affected_rows);

// Exibir template
$smarty->display('sql-query.tpl');

// Finalizar buffer de saída
ob_end_flush();
?>