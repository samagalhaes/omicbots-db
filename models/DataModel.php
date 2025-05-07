<?php
// models/DataModel.php - Classe modelo para operações de dados

class DataModel {
    private $conn;
    
    /**
     * Construtor
     * @param mysqli $conn Conexão com o banco de dados
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Obter todas as culturas disponíveis
     * @return array Lista de culturas
     */
    public function getCrops() {
        $crops = [];
        $sql = "SELECT DISTINCT Crop FROM cropid ORDER BY Crop";
        
        if ($result = mysqli_query($this->conn, $sql)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $crops[] = $row['Crop'];
            }
            mysqli_free_result($result);
        } else {
            logMessage("Error fetching crops: " . mysqli_error($this->conn), 'error');
        }
        
        return $crops;
    }
    
    /**
     * Obter todos os projetos disponíveis
     * @return array Lista de projetos
     */
    public function getProjects() {
        $projects = [];
        $sql = "SELECT DISTINCT Project FROM cropid ORDER BY Project";
        
        if ($result = mysqli_query($this->conn, $sql)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $projects[] = $row['Project'];
            }
            mysqli_free_result($result);
        } else {
            logMessage("Error fetching projects: " . mysqli_error($this->conn), 'error');
        }
        
        return $projects;
    }
    
    /**
     * Obter parâmetros disponíveis por categoria
     * @param string $table Nome da tabela
     * @return array Lista de parâmetros
     */
    public function getParameters($table) {
        $parameters = [];
        
        // Determinar o nome da coluna baseado na tabela
        $columnName = 'Parameter';
        if ($table == 'xrf') {
            $columnName = 'Molecule';
        } elseif ($table == 'spectra') {
            $columnName = 'Wavelength';
        }
        
        $sql = "SELECT DISTINCT $columnName FROM $table ORDER BY $columnName";
        
        if ($result = mysqli_query($this->conn, $sql)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $parameters[] = $row[$columnName];
            }
            mysqli_free_result($result);
        } else {
            logMessage("Error fetching parameters from $table: " . mysqli_error($this->conn), 'error');
        }
        
        return $parameters;
    }
    
    /**
     * Consultar dados com base em filtros selecionados
     * @param array $filters Filtros de consulta
     * @return array Resultado da consulta
     */
    public function queryData($filters) {
        $tables = ['lab_measures', 'hormones', 'ecophysio', 'genes', 'xrf', 'spectra'];
        $joins = [];
        $conditions = [];
        $selectedFields = [];
        
        // Adicionar tabela principal
        $mainTable = "cropid";
        $baseQuery = "SELECT c.ID as crop_id, c.Code_field, c.FW_sample, c.Date, c.Test_site, 
                     c.Crop, c.Cultivar, c.Irrigation, c.Coord, c.Project";
        
        // Construir as junções e campos selecionados com base nas categorias escolhidas
        foreach ($tables as $table) {
            if (isset($filters['categories']) && in_array($table, $filters['categories'])) {
                $tableAlias = substr($table, 0, 1);
                $joins[] = "LEFT JOIN $table $tableAlias ON c.ID = $tableAlias.ID";
                
                // Adicionar campos da tabela aos campos selecionados
                if ($table == 'lab_measures' || $table == 'hormones' || $table == 'ecophysio' || $table == 'genes') {
                    $selectedFields[] = "$tableAlias.Parameter as {$tableAlias}_parameter";
                    $selectedFields[] = "$tableAlias.Value as {$tableAlias}_value";
                    if ($table == 'lab_measures' || $table == 'hormones' || $table == 'genes') {
                        $selectedFields[] = "$tableAlias.Laboratory as {$tableAlias}_laboratory";
                    }
                    if ($table == 'lab_measures') {
                        $selectedFields[] = "$tableAlias.Morphology as {$tableAlias}_morphology";
                    }
                } elseif ($table == 'xrf') {
                    $selectedFields[] = "$tableAlias.Molecule as x_molecule";
                    $selectedFields[] = "$tableAlias.Value as x_value";
                    $selectedFields[] = "$tableAlias.Error as x_error";
                } elseif ($table == 'spectra') {
                    $selectedFields[] = "$tableAlias.Wavelength as s_wavelength";
                    $selectedFields[] = "$tableAlias.Intensity as s_intensity";
                    $selectedFields[] = "$tableAlias.Morphology as s_morphology";
                    $selectedFields[] = "$tableAlias.Spectra_device as s_device";
                    $selectedFields[] = "$tableAlias.Local as s_local";
                }
            }
        }
        
        // Aplicar filtros
        if (!empty($filters['crop']) && $filters['crop'] != 'all') {
            $conditions[] = "c.Crop = '" . mysqli_real_escape_string($this->conn, $filters['crop']) . "'";
        }
        
        if (!empty($filters['project']) && $filters['project'] != 'all') {
            $conditions[] = "c.Project = '" . mysqli_real_escape_string($this->conn, $filters['project']) . "'";
        }
        
        if (!empty($filters['date_from']) && validateDate($filters['date_from'])) {
            $conditions[] = "c.Date >= '" . mysqli_real_escape_string($this->conn, $filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to']) && validateDate($filters['date_to'])) {
            $conditions[] = "c.Date <= '" . mysqli_real_escape_string($this->conn, $filters['date_to']) . "'";
        }
        
        // Construir a consulta completa
        $query = $baseQuery;
        
        if (!empty($selectedFields)) {
            $query .= ", " . implode(", ", $selectedFields);
        }
        
        $query .= " FROM $mainTable c";
        
        if (!empty($joins)) {
            $query .= " " . implode(" ", $joins);
        }
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Adicionar limite se necessário
        if (isset($filters['limit']) && $filters['limit'] > 0) {
            $query .= " LIMIT " . (int)$filters['limit'];
        }
        
        // Executar a consulta
        logMessage("Executing query: " . $query, 'debug');
        $result = mysqli_query($this->conn, $query);
        
        if (!$result) {
            logMessage("Query error: " . mysqli_error($this->conn), 'error');
            return ["error" => "Query error: " . mysqli_error($this->conn)];
        }
        
        // Obter os resultados
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        mysqli_free_result($result);
        
        return ["data" => $data, "query" => $query];
    }
    
    /**
     * Exportar dados para CSV
     * @param array $data Dados a serem exportados
     * @param string $filename Nome do arquivo
     */
    public function exportToCSV($data, $filename = "agricultural_data.csv") {
        // Definir cabeçalhos para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Criar um ponteiro para saída
        $output = fopen('php://output', 'w');
        
        // Se tivermos dados
        if (!empty($data)) {
            // Escrever cabeçalhos
            fputcsv($output, array_keys($data[0]));
            
            // Escrever cada linha de dados
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar dados para Excel (XLSX)
     * @param array $data Dados a serem exportados
     * @param string $filename Nome do arquivo
     */
    public function exportToExcel($data, $filename = "agricultural_data.xlsx") {
        // Verificar se a biblioteca PhpSpreadsheet está disponível
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            logMessage("PhpSpreadsheet not available", 'error');
            setFlashMessage("Erro ao exportar para Excel: Biblioteca PhpSpreadsheet não encontrada.", 'error');
            header("Location: index.php");
            exit;
        }
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Adicionar cabeçalhos
        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $column = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($column, 1, $header);
                $column++;
            }
            
            // Adicionar dados
            $row = 2;
            foreach ($data as $dataRow) {
                $column = 1;
                foreach ($dataRow as $value) {
                    $sheet->setCellValueByColumnAndRow($column, $row, $value);
                    $column++;
                }
                $row++;
            }
        }
        
        // Configurar estilos
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => '4F81BD',
                ],
            ],
            'font' => [
                'color' => [
                    'rgb' => 'FFFFFF',
                ]
            ]
        ];
        
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($styleArray);
        
        // Ajustar largura das colunas automaticamente
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Criar o escritor para XLSX
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Definir cabeçalhos para download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Salvar para output
        $writer->save('php://output');
        exit;
    }
}
?>