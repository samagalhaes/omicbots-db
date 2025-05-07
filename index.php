<?php
// index.php - Arquivo principal da aplicação

// Incluir o autoloader do Composer
require 'vendor/autoload.php';

// Incluir arquivos de configuração e funções
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'models/DataModel.php';

// Inicializar o Smarty
$smarty = new Smarty();
$smarty->setTemplateDir('templates');
$smarty->setCompileDir('templates_c');
$smarty->setCacheDir('cache');
$smarty->setConfigDir('configs');

// Inicializar o modelo de dados
$dataModel = new DataModel($conn);

// Obter categorias, culturas e projetos para os filtros
$crops = $dataModel->getCrops();
$projects = $dataModel->getProjects();

// Inicializar variáveis
$data = [];
$message = '';
$totalRecords = 0;

// Processar formulário se enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filters = [
        'categories' => isset($_POST['categories']) ? $_POST['categories'] : [],
        'crop' => isset($_POST['crop']) ? $_POST['crop'] : 'all',
        'project' => isset($_POST['project']) ? $_POST['project'] : 'all',
        'date_from' => isset($_POST['date_from']) ? $_POST['date_from'] : '',
        'date_to' => isset($_POST['date_to']) ? $_POST['date_to'] : '',
        'limit' => isset($_POST['limit']) ? (int)$_POST['limit'] : 1000
    ];
    
    $result = $dataModel->queryData($filters);
    
    if (isset($result['error'])) {
        $message = $result['error'];
    } else {
        $data = $result['data'];
        $totalRecords = count($data);
        
        // Processar pedidos de download
        if (isset($_POST['download'])) {
            $format = $_POST['format'];
            $filename = "agricultural_data_" . date('Y-m-d') . "." . strtolower($format);
            
            if ($format == 'CSV') {
                $dataModel->exportToCSV($data, $filename);
            } elseif ($format == 'XLSX') {
                $dataModel->exportToExcel($data, $filename);
            }
        }
    }
}

// Passar variáveis para o template
$smarty->assign('crops', $crops);
$smarty->assign('projects', $projects);
$smarty->assign('data', $data);
$smarty->assign('message', $message);
$smarty->assign('totalRecords', $totalRecords);
$smarty->assign('post', $_POST);
$smarty->assign('currentYear', date('Y'));

// Exibir o template
$smarty->display('index.tpl');
?>