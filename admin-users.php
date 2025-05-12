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



// Check if user is logged in and is admin
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

// Get available projects
$projects = $dataManager->getProjects();

// Initialize error and success messages
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Determine action
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create_user':
                // Validate and create new user
                $userData = [
                    'username' => $_POST['username'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'project' => $_POST['project'] ?? '',
                    'role' => $_POST['role'] ?? 'user'
                ];

                if ($current_user->createUserByAdmin($userData)) {
                    $success = 'Usuário criado com sucesso';
                }
                break;

            case 'update_project':
                // Update user project
                $userId = $_POST['user_id'] ?? 0;
                $newProject = $_POST['project'] ?? '';

                try {
                    // Carregar usuário para atualização
                    $userToUpdate = new User($conn);
                    $userToUpdate->id = $userId;

                    // Carregar dados do usuário atual
                    $userData = $userToUpdate->getUserById($userId);
                    $userToUpdate->username = $userData['username'];
                    $userToUpdate->project = $userData['project'];
                    $userToUpdate->role = $userData['role'];

                    // Substituir projetos
                    if ($userToUpdate->replaceProjects($newProject)) {
                        $success = 'Projetos do usuário atualizados com sucesso';
                    } else {
                        $error = 'Falha ao atualizar projetos do usuário';
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                break;

            case 'delete_user':
                // Delete user
                $userId = $_POST['user_id'] ?? 0;

                if ($current_user->deleteUser($userId)) {
                    $success = 'Usuário removido com sucesso';
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Retrieve users
try {
    $users = $current_user->getAllUsers();
} catch (Exception $e) {
    $error = $e->getMessage();
    $users = [];
}

// Assign template variables
$smarty->assign('users', $users);
$smarty->assign('projects', $projects);
$smarty->assign('error', $error);
$smarty->assign('success', $success);
$smarty->assign('current_user', [
    'id' => $current_user->id,
    'username' => $current_user->username,
    'role' => $current_user->role
]);

// Display the template
$smarty->display('admin-users.tpl');

// End output buffering
ob_end_flush();
