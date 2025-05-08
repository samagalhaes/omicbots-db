<?php
require_once 'vendor/autoload.php';  // For Composer autoloading
require_once 'config/config.php';    // Database configuration
require_once 'classes/Database.php'; // Database connection class
require_once 'classes/DataManager.php'; // Data management class

// Initialize Smarty
$smarty = new Smarty();
$smarty->setTemplateDir('templates');
$smarty->setCompileDir('templates_c');
$smarty->setCacheDir('cache');
$smarty->setConfigDir('configs');

// Initialize Database connection
$db = new Database();
$conn = $db->getConnection();

// Initialize Data Manager
$dataManager = new DataManager($conn);

// Get filter options for different categories
$spectraDevices = $dataManager->getSpectraDevices();
$years = $dataManager->getYears();
$cropTypes = $dataManager->getCropTypes();

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
        
        // Generate and download the file
        $dataManager->downloadData($filters, $downloadFormat);
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
        
        // Get filtered data
        $data = $dataManager->getData($filters, 5);
    }
} else {
    // Default: get limited preview data with default filter
    $data = $dataManager->getData($filters, 5); // Get first 5 rows
}

// Count selected records and columns
$recordCount = count($data);
$columnCount = $data ? count($data[0]) : 0;

// Assign variables to Smarty
$smarty->assign('spectraDevices', $spectraDevices);
$smarty->assign('years', $years);
$smarty->assign('cropTypes', $cropTypes);
$smarty->assign('data', $data);
$smarty->assign('filters', $filters);
$smarty->assign('recordCount', $recordCount);
$smarty->assign('columnCount', $columnCount);

// Display the template
$smarty->display('index.tpl');
?>