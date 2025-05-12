<?php
// Start output buffering
ob_start();

// Initialize session
session_start();

require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/DataManager.php';

// Função para proteção de rota de admin
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        // Log tentativa de acesso não autorizado
        error_log("Tentativa não autorizada de acesso à área admin por: " . 
                   ($_SESSION['username'] ?? 'usuário não identificado'));
        
        // Redirecionar com mensagem de erro
        $_SESSION['error_message'] = 'Acesso restrito. Apenas administradores podem acessar esta página.';
        
        // Redirecionar para página de login
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

// Initialize User and DataManager
$current_user = new User($conn);
$current_user->id = $_SESSION['user_id'];
$current_user->username = $_SESSION['username'];
$current_user->role = $_SESSION['user_role'];

$dataManager = new DataManager($conn);

// Inicializar variáveis
$error = '';
$success = '';

// Processar ações de formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Determinar ação
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add_project':
                // Adicionar novo projeto
                $newProject = $_POST['new_project'] ?? '';
                
                if ($dataManager->addNewProject($newProject, $current_user)) {
                    $success = "Projeto '$newProject' adicionado com sucesso";
                }
                break;

            case 'remove_project':
                // Remover projeto
                $projectToRemove = $_POST['project'] ?? '';
                
                if ($dataManager->removeProject($projectToRemove, $current_user)) {
                    $success = "Projeto '$projectToRemove' removido com sucesso";
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obter lista de projetos
try {
    $projects = $dataManager->getProjects();
} catch (Exception $e) {
    $error = $e->getMessage();
    $projects = [];
}

// Informações sobre os projetos
$projectStats = [];
foreach ($projects as $project) {
    // Contar usuários por projeto
    $countQuery = "SELECT COUNT(*) FROM users WHERE project LIKE :project";
    $stmt = $conn->prepare($countQuery);
    $stmt->bindValue(':project', '%' . $project . '%');
    $stmt->execute();
    
    $projectStats[$project] = [
        'user_count' => $stmt->fetchColumn(),
    ];
}

// Atribuir variáveis para o template
$smarty->assign('projects', $projects);
$smarty->assign('project_stats', $projectStats);
$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('current_user', [
    'id' => $current_user->id,
    'username' => $current_user->username,
    'role' => $current_user->role
]);

// Exibir template
$smarty->display('admin-projects.tpl');

// Finalizar buffer de saída
ob_end_flush();
?>