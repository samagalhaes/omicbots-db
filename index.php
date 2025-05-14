<?php
// Start output buffering immediately
ob_start();

// Start session with strict mode
ini_set('session.use_strict_mode', 1);
session_start();

require_once 'vendor/autoload.php';  // For Composer autoloading
require_once 'config/config.php';    // Database configuration
require_once 'classes/Database.php'; // Database connection class
require_once 'classes/DataManager.php'; // Data management class
require_once 'classes/User.php'; // User management class

// Initialize Smarty
$smarty = new Smarty();
$smarty->setTemplateDir('templates');
$smarty->setCompileDir('templates_c');
$smarty->setCacheDir('cache');
$smarty->setConfigDir('configs');

// Initialize Database connection
$db = new Database();
$conn = $db->getConnection();

// Initialize User and Data Manager
$user = new User($conn);
$dataManager = new DataManager($conn);

// Default template variables
$logged_in = false;
$register_mode = false;
$login_error = '';
$register_error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine action
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            // Login attempt
            $user->username = $_POST['username'];
            $user->password = $_POST['password'];

            if ($user->login()) {
                // Successful login
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['user_project'] = $user->project;
                $_SESSION['user_role'] = $user->role;
                $logged_in = true;
            } else {
                // Login failed
                $login_error = 'Invalid username or password';
                $register_mode = false;
            }
            break;
    }
}

// Logout handling
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the same page to start a new session
    ob_end_clean();
    header('Location: index.php');
    exit();
}

// Check if user is logged in from session
if (isset($_SESSION['user_id'])) {
    $logged_in = true;
    $user->id = $_SESSION['user_id'];
    $user->username = $_SESSION['username'];
    $user->project = $_SESSION['user_project'];
    $user->role = $_SESSION['user_role'];
}

$authorized_projects = explode(',', $user->project);
// $authorized_projects[] = "Omicbots";

// Get filter options for different categories
$spectraDevices = $dataManager->getSpectraDevices();
$years = $dataManager->getYears();
$cropTypes = $dataManager->getCropTypes();
$projects = $dataManager->getProjects();

if ($user->role === 'user' || !$logged_in) {
    // Filter projects based on user role
    $projects = array_filter($projects, function ($project) use ($authorized_projects) {
        // Remove spaces from project name
        $authorized_projects = str_replace(' ', '', $authorized_projects);
        return in_array($project, $authorized_projects);
    });
}

// Process form submission if any
$filters = [];
$data = [];
$downloadFormat = 'csv';

// Após obter os dados para a lista de dispositivos
$spectraDevices = $dataManager->getSpectraDevices();
$years = $dataManager->getYears();
$cropTypes = $dataManager->getCropTypes();

// Se não houver seleção de dispositivo especificada, 
// use o primeiro dispositivo como padrão (se houver algum)
if (!isset($_POST['spectra_device']) && !empty($spectraDevices)) {
    $filters['spectra_device'] = $spectraDevices[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a download request
    if (isset($_POST['download']) && $_POST['download'] === 'true') {
        $downloadFormat = isset($_POST['format']) ? $_POST['format'] : 'csv';

        // Get filters from POST data
        if (isset($_POST['spectra_device']) && !empty($_POST['spectra_device'])) {
            $filters['spectra_device'] = $_POST['spectra_device'];
        }

        if (isset($_POST['years']) && is_array($_POST['years'])) {
            $filters['years'] = $_POST['years'];
        }

        if (isset($_POST['crop_types']) && is_array($_POST['crop_types'])) {
            $filters['crop_types'] = $_POST['crop_types'];
        }

        if (isset($_POST['projects']) && is_array($_POST['projects'])) {
            $filters['projects'] = $_POST['projects'];
        }

        if (isset($_POST['data_categories']) && is_array($_POST['data_categories'])) {
            $filters['data_categories'] = $_POST['data_categories'];
        }

        if ($user->role === 'user' || !$logged_in) {

            $authorized_projects = explode(',', $user->project);
            $authorized_projects[] = "Omicbots";

            // Verify all $filters['projects'] are in $authorized_projects
            if (isset($filters['projects']) && is_array($filters['projects'])) {
                foreach ($filters['projects'] as $project) {
                    if (!in_array($project, $authorized_projects)) {
                        // If an unauthorized project is found, throw an error or handle it
                        die('Unauthorized project access detected.');
                    }
                }
            }
        }

        // // Generate and download the file
        // $dataManager->downloadData($filters, $downloadFormat);
        
        // Verificar o tamanho total dos dados
        $totalRecords = $dataManager->countTotalRecordsForDownload($filters);

        // Definir chunk size com base no tamanho total dos dados
        $chunkSize = 50000; // Ajuste conforme necessário

        // Adicionar opção de download de grandes volumes
        try {
            $dataManager->downloadLargeData($filters, $downloadFormat, $chunkSize);
        } catch (Exception $e) {
            // Lidar com erro de download
            $error = "Erro no download: " . $e->getMessage();
            // Redirecionar com mensagem de erro
            $_SESSION['download_error'] = $error;
        }
        
        ob_end_clean();
        exit; // Stop execution after download
    } else {
        // It's a filter request
        if (isset($_POST['spectra_device']) && !empty($_POST['spectra_device'])) {
            $filters['spectra_device'] = $_POST['spectra_device'];
        }

        if (isset($_POST['years']) && is_array($_POST['years'])) {
            $filters['years'] = $_POST['years'];
        }

        if (isset($_POST['crop_types']) && is_array($_POST['crop_types'])) {
            $filters['crop_types'] = $_POST['crop_types'];
        }

        if (isset($_POST['projects']) && is_array($_POST['projects'])) {
            $filters['projects'] = $_POST['projects'];
        }

        if (isset($_POST['data_categories']) && is_array($_POST['data_categories'])) {
            $filters['data_categories'] = $_POST['data_categories'];
        }

        // Get filtered data
        $data = $dataManager->getData($filters,100);
    }
} else {
    // Default: get limited preview data with default filter
    $data = $dataManager->getData($filters, 10); // Get first 10 rows
}

// Count selected records and columns
$recordCount = count($data);
$columnCount = $data ? count($data[0]) : 0;

$dataCategories = $dataManager->getAvailableDataCategories($filters);

 // Atribuir variáveis ao template
 $smarty->assign('dataManager', $dataManager);

// Assign variables to Smarty
$smarty->assign('spectraDevices', $spectraDevices);
$smarty->assign('years', $years);
$smarty->assign('cropTypes', $cropTypes);
$smarty->assign('projects', $projects);
$smarty->assign('dataCategories', $dataCategories);
$smarty->assign('data', $data);
$smarty->assign('filters', $filters);
$smarty->assign('recordCount', $recordCount);
$smarty->assign('columnCount', $columnCount);

$smarty->assign('logged_in', $logged_in);
$smarty->assign('register_mode', $register_mode);
$smarty->assign('login_error', $login_error);
$smarty->assign('register_error', $register_error);

// Assign user info to template
$smarty->assign('user', [
    'id' => $user->id,
    'username' => $user->username,
    'project' => $user->project,
    'role' => $user->role
]);


// Display the template
$smarty->display('index.tpl');
ob_end_flush();
