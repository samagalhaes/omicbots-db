<?php
class DataManager
{
    private $conn;

    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Get list of available spectra devices
     * @return array List of spectra devices
     */
    public function getSpectraDevices()
    {
        $query = "SELECT DISTINCT Spectra_device FROM spectra ORDER BY Spectra_device";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $devices = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $devices[] = $row['Spectra_device'];
        }

        return $devices;
    }

    /**
     * Get list of available years
     * @return array List of years
     */
    public function getYears()
    {
        $query = "SELECT DISTINCT YEAR(c.Date) as Year FROM cropid c ORDER BY Year DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $years = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $years[] = $row['Year'];
        }

        return $years;
    }

    /**
     * Get list of available crop types
     * @return array List of crop types
     */
    public function getCropTypes()
    {
        $query = "SELECT DISTINCT Crop FROM cropid ORDER BY Crop";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $crops = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $crops[] = $row['Crop'];
        }

        return $crops;
    }

    /**
     * Obtém as wavelengths disponíveis para um dispositivo espectral específico
     * @param string $spectraDevice O dispositivo de espectro (ex: 'NIRS', 'FTIR')
     * @param array $additionalFilters Filtros adicionais para aplicar (opcional)
     * @return array Lista de wavelengths disponíveis, ordenadas
     */
    public function getAvailableWavelengths($spectraDevice, $additionalFilters = [])
    {
        $query = "
        SELECT DISTINCT 
            s.Wavelength
        FROM 
            spectra s
            JOIN cropid c ON s.ID = c.ID
        WHERE 
            s.Spectra_device = :device
    ";

        $params = [':device' => $spectraDevice];

        // Aplicar filtros adicionais se fornecidos
        if (isset($additionalFilters['years']) && !empty($additionalFilters['years'])) {
            $placeholders = [];
            foreach ($additionalFilters['years'] as $key => $year) {
                $paramName = ":year" . $key;
                $placeholders[] = $paramName;
                $params[$paramName] = $year;
            }
            $query .= " AND YEAR(c.Date) IN (" . implode(", ", $placeholders) . ")";
        }

        if (isset($additionalFilters['crop_types']) && !empty($additionalFilters['crop_types'])) {
            $placeholders = [];
            foreach ($additionalFilters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $placeholders[] = $paramName;
                $params[$paramName] = $crop;
            }
            $query .= " AND c.Crop IN (" . implode(", ", $placeholders) . ")";
        }

        // Ordenar por wavelength
        $query .= " ORDER BY s.Wavelength ASC";

        try {
            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();

            $wavelengths = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $wavelengths[] = $row['Wavelength'];
            }

            return $wavelengths;
        } catch (PDOException $e) {
            // Log do erro
            error_log("Erro ao obter wavelengths: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get data based on filters with opção para formato pivotado de wavelengths
     * @param array $filters Array of filter criteria
     * @param int $limit Limit number of records returned (0 = no limit)
     * @param int $offset Offset for pagination
     * @param bool $pivotWavelengths Se true, retorna dados com wavelengths como colunas
     * @return array Filtered data
     */
    public function getData($filters = [], $limit = 0, $offset = 0)
    {

        // Determinar as wavelengths a serem usadas
        $spectraDevice = isset($filters['spectra_device']) ? $filters['spectra_device'] : null;

        if (!$spectraDevice) {
            // Se não há dispositivo definido, não podemos pivotar
            return [
                'metadata' => ['error' => 'Spectra device must be specified for pivoted data'],
                'data' => []
            ];
        }

        $specificWavelengths = $this->getAvailableWavelengths($spectraDevice, $filters);

        // Se não houver wavelengths, retornar array vazio
        if (empty($specificWavelengths)) {
            return [
                'metadata' => ['error' => 'No wavelengths available for the selected filters'],
                'data' => []
            ];
        }

        // Começar a construir a consulta
        $query = "
            SELECT 
                s.ID,
                c.Date,
                YEAR(c.Date) as Year,
                s.Spectra_device,
                c.Crop,
                c.Cultivar,
                c.Test_site,
                c.Project,
                s.Morphology,
                s.Local,
        ";

        // Adicionar as colunas dinâmicas para cada wavelength
        $pivotColumns = [];
        foreach ($specificWavelengths as $wavelength) {
            // Formatar o nome da coluna (substituir ponto por underscore)
            $columnName = "W" . str_replace('.', '_', $wavelength);
            $pivotColumns[] = "MAX(CASE WHEN s.Wavelength = " . $this->conn->quote($wavelength) . " THEN s.Intensity ELSE NULL END) AS `" . $columnName . "`";
        }

        // Adicionar as colunas pivot à consulta
        $query .= implode(",\n                ", $pivotColumns);

        // Completar a consulta com as tabelas e cláusulas GROUP BY
        $query .= "
            FROM 
                spectra s
                JOIN cropid c ON s.ID = c.ID
            WHERE 
                1=1
        ";

        $params = [];

        // Adicionar filtros
        // Filtro de dispositivo espectral
        if (isset($filters['spectra_device']) && !empty($filters['spectra_device'])) {
            $query .= " AND s.Spectra_device = :device";
            $params[':device'] = $filters['spectra_device'];
        }

        // Filtros de wavelength (aplicados apenas para restringir o conjunto de dados base)
        if (isset($filters['wavelength_min']) && is_numeric($filters['wavelength_min'])) {
            $query .= " AND s.Wavelength >= :wavelength_min";
            $params[':wavelength_min'] = $filters['wavelength_min'];
        }

        if (isset($filters['wavelength_max']) && is_numeric($filters['wavelength_max'])) {
            $query .= " AND s.Wavelength <= :wavelength_max";
            $params[':wavelength_max'] = $filters['wavelength_max'];
        }

        // Filtros adicionais
        if (isset($filters['years']) && !empty($filters['years'])) {
            $placeholders = [];
            foreach ($filters['years'] as $key => $year) {
                $paramName = ":year" . $key;
                $placeholders[] = $paramName;
                $params[$paramName] = $year;
            }
            $query .= " AND YEAR(c.Date) IN (" . implode(", ", $placeholders) . ")";
        }

        if (isset($filters['crop_types']) && !empty($filters['crop_types'])) {
            $placeholders = [];
            foreach ($filters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $placeholders[] = $paramName;
                $params[$paramName] = $crop;
            }
            $query .= " AND c.Crop IN (" . implode(", ", $placeholders) . ")";
        }

        // Restringir a query para incluir apenas as wavelengths de interesse
        // (isto melhora o desempenho quando o número de wavelengths é grande)
        $wavePlaceholders = [];
        foreach ($specificWavelengths as $key => $wavelength) {
            $paramName = ":wave" . $key;
            $wavePlaceholders[] = $paramName;
            $params[$paramName] = $wavelength;
        }
        $query .= " AND s.Wavelength IN (" . implode(", ", $wavePlaceholders) . ")";

        // Adicionar cláusula GROUP BY para o pivot
        $query .= "
            GROUP BY 
                s.ID, 
                YEAR(c.Date),
                s.Spectra_device,
                c.Crop,
                c.Cultivar,
                c.Test_site,
                c.Project,
                s.Morphology,
                s.Local
        ";

        // Ordenação
        $query .= " ORDER BY s.ID";

        // Limite
        if ($limit > 0) {
            $query .= " LIMIT :limit";
            $params[':limit'] = $limit;

            if ($offset > 0) {
                $query .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }


        try {
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Se for formato pivotado, adicionar metadados
            if ($pivotWavelengths) {
                // Adicionar as wavelengths usadas como metadados para uso na interface
                $metadata = [
                    'wavelengths' => $specificWavelengths,
                    'column_mapping' => []
                ];

                // Criar mapeamento entre wavelengths originais e nomes de colunas formatados
                foreach ($specificWavelengths as $wavelength) {
                    $columnName = "W" . str_replace('.', '_', $wavelength);
                    $metadata['column_mapping'][$wavelength] = $columnName;
                }

                return [
                    'metadata' => $metadata,
                    'data' => $result
                ];
            } else {
                // Formato original, retornar apenas os dados
                return $result;
            }
        } catch (PDOException $e) {
            // Log do erro
            error_log("Erro na consulta: " . $e->getMessage() . "\nConsulta: " . $query);

            if ($pivotWavelengths) {
                return [
                    'metadata' => ['error' => $e->getMessage()],
                    'data' => []
                ];
            } else {
                return [];
            }
        }
    }

    /**
     * Generate and download data in specified format
     * @param array $filters Array of filter criteria
     * @param string $format Format (csv, excel, json)
     */
    public function downloadData($filters = [], $format = 'csv')
    {
        // Get data based on filters
        $data = $this->getData($filters);

        // Set appropriate headers based on format
        switch ($format) {
            case 'excel':
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="agricultural_data.xls"');
                $this->generateExcel($data);
                break;

            case 'json':
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="agricultural_data.json"');
                echo json_encode($data, JSON_PRETTY_PRINT);
                break;

            case 'csv':
            default:
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="agricultural_data.csv"');
                $this->generateCSV($data);
                break;
        }
    }

    /**
     * Generate CSV file from data
     * @param array $data Data to convert to CSV
     */
    private function generateCSV($data)
    {
        if (empty($data)) {
            echo "No data found";
            return;
        }

        $output = fopen('php://output', 'w');

        // Add headers (column names)
        fputcsv($output, array_keys($data[0]));

        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
    }

    /**
     * Generate Excel file from data
     * @param array $data Data to convert to Excel
     */
    private function generateExcel($data)
    {
        if (empty($data)) {
            echo "No data found";
            return;
        }

        // This is a simplified version. In a real application, 
        // you would use a library like PhpSpreadsheet
        echo "<table border='1'>";

        // Headers
        echo "<tr>";
        foreach (array_keys($data[0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";

        // Data rows
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }

        echo "</table>";
    }

    // Faça as mesmas alterações no método buildFilteredQuery na correção de memória
    private function buildFilteredQuery($filters = [], $limit = 0, $offset = 0)
    {
        $query = "
        SELECT 
            s.ID,
            YEAR(c.Date) as Year,
            s.Spectra_device,
            c.Crop,
            c.Cultivar,
            s.Wavelength,
            s.Intensity,
            s.Morphology,
            s.Local
        FROM 
            spectra s
            JOIN cropid c ON s.ID = c.ID
        WHERE 
            1=1
    ";

        // Modificação para suportar tanto checkbox (spectra_devices) quanto radio button (spectra_device)
        if (isset($filters['spectra_devices']) && !empty($filters['spectra_devices'])) {
            // Versão para checkbox (múltipla seleção)
            $placeholders = [];
            foreach ($filters['spectra_devices'] as $key => $device) {
                $paramName = ":device" . $key;
                $placeholders[] = $paramName;
            }
            $query .= " AND s.Spectra_device IN (" . implode(", ", $placeholders) . ")";
        } elseif (isset($filters['spectra_device']) && !empty($filters['spectra_device'])) {
            // Versão para radio button (seleção única)
            $query .= " AND s.Spectra_device = :device";
        }

        // Restante do código permanece o mesmo
        if (isset($filters['years']) && !empty($filters['years'])) {
            $placeholders = [];
            foreach ($filters['years'] as $key => $year) {
                $paramName = ":year" . $key;
                $placeholders[] = $paramName;
            }
            $query .= " AND YEAR(c.Date) IN (" . implode(", ", $placeholders) . ")";
        }

        if (isset($filters['crop_types']) && !empty($filters['crop_types'])) {
            $placeholders = [];
            foreach ($filters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $placeholders[] = $paramName;
            }
            $query .= " AND c.Crop IN (" . implode(", ", $placeholders) . ")";
        }

        // Order by ID and wavelength
        $query .= " ORDER BY s.ID, s.Wavelength";

        // Apply limit and offset if specified
        if ($limit > 0) {
            $query .= " LIMIT :limit";

            if ($offset > 0) {
                $query .= " OFFSET :offset";
            }
        }

        return $query;
    }

    // E também no método bindFilterParams
    private function bindFilterParams($stmt, $filters = [], $limit = 0, $offset = 0)
    {
        // Modificação para suportar tanto checkbox (spectra_devices) quanto radio button (spectra_device)
        if (isset($filters['spectra_devices']) && !empty($filters['spectra_devices'])) {
            // Versão para checkbox (múltipla seleção)
            foreach ($filters['spectra_devices'] as $key => $device) {
                $paramName = ":device" . $key;
                $stmt->bindValue($paramName, $device);
            }
        } elseif (isset($filters['spectra_device']) && !empty($filters['spectra_device'])) {
            // Versão para radio button (seleção única)
            $stmt->bindValue(':device', $filters['spectra_device']);
        }

        // Restante do código permanece o mesmo
        if (isset($filters['years']) && !empty($filters['years'])) {
            foreach ($filters['years'] as $key => $year) {
                $paramName = ":year" . $key;
                $stmt->bindValue($paramName, $year);
            }
        }

        if (isset($filters['crop_types']) && !empty($filters['crop_types'])) {
            foreach ($filters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $stmt->bindValue($paramName, $crop);
            }
        }

        // Bind limit and offset
        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            if ($offset > 0) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }
    }
}
?>