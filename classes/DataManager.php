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
        $query = "
            SELECT DISTINCT 
                CASE 
                    WHEN c.Date IS NULL OR YEAR(c.Date) = 0 THEN 0
                    ELSE YEAR(c.Date) 
                END as Year
            FROM 
                cropid c 
            ORDER BY 
                Year DESC
        ";
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

    public function getProjects()
    {
        $query = "
            SELECT DISTINCT 
                CASE 
                    WHEN c.Project IS NULL OR TRIM(c.Project) = '' THEN 'N/A'
                    ELSE c.Project 
                END as Project
            FROM 
                cropid c 
            ORDER BY 
                Project ASC
        ";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $projects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $projects[] = $row['Project'];
            }

            return $projects;
        } catch (PDOException $e) {
            error_log("Erro ao obter lista de projetos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém as categorias de dados disponíveis para filtragem
     * @return array Lista de categorias de dados disponíveis
     */
    public function getDataCategories()
    {
        // Lista estática de categorias de dados conforme o schema do banco
        return [
            'lab_measures' => 'Laboratory Measures',
            'ecophysio' => 'Ecophysio',
            'xrf' => 'XRF',
            'hormones' => 'Hormones',
            'genes' => 'Genes',
        ];
    }

    public function getAvailableDataCategories($filters = [])
    {
        $allCategories = $this->getDataCategories();
        $availableCategories = [];

        // Clonar os filtros para remover filtros de categoria (se existirem)
        $filtersWithoutCategories = $filters;
        unset($filtersWithoutCategories['data_categories']);

        // Para cada categoria, verificar se existem dados com os filtros atuais
        foreach ($allCategories as $categoryKey => $categoryName) {
            // Construir consulta que conta registros na categoria com os filtros atuais
            $query = "
                SELECT COUNT(DISTINCT t.ID) as count
                FROM {$categoryKey} t
                JOIN cropid c ON t.ID = c.ID
                WHERE 1=1
            ";

            $params = [];

            // Aplicar filtros existentes (ano, projeto, etc.)
            // Filtro de ano
            print_r($filtersWithoutCategories);
            if (isset($filtersWithoutCategories['years']) && !empty($filtersWithoutCategories['years'])) {
                $hasNA = false;
                $yearValues = [];

                foreach ($filtersWithoutCategories['years'] as $year) {
                    if ($year == 0) {
                        $hasNA = true;
                    } else {
                        $yearValues[] = $year;
                    }
                }

                if (!empty($yearValues) && $hasNA) {
                    $placeholders = [];
                    foreach ($yearValues as $key => $year) {
                        $paramName = ":year" . $key;
                        $placeholders[] = $paramName;
                        $params[$paramName] = $year;
                    }

                    $query .= " AND (YEAR(c.Date) IN (" . implode(", ", $placeholders) . ") OR c.Date IS NULL OR YEAR(c.Date) = 0)";
                } else if (!empty($yearValues)) {
                    $placeholders = [];
                    foreach ($yearValues as $key => $year) {
                        $paramName = ":year" . $key;
                        $placeholders[] = $paramName;
                        $params[$paramName] = $year;
                    }

                    $query .= " AND YEAR(c.Date) IN (" . implode(", ", $placeholders) . ")";
                } else if ($hasNA) {
                    $query .= " AND (c.Date IS NULL OR YEAR(c.Date) = 0)";
                }
            }

            // Filtro de projeto
            if (isset($filtersWithoutCategories['projects']) && !empty($filtersWithoutCategories['projects'])) {
                $hasNA = false;
                $projectValues = [];

                foreach ($filtersWithoutCategories['projects'] as $project) {
                    if ($project === 'N/A') {
                        $hasNA = true;
                    } else {
                        $projectValues[] = $project;
                    }
                }

                if (!empty($projectValues) && $hasNA) {
                    $placeholders = [];
                    foreach ($projectValues as $key => $project) {
                        $paramName = ":project" . $key;
                        $placeholders[] = $paramName;
                        $params[$paramName] = $project;
                    }

                    $query .= " AND (c.Project IN (" . implode(", ", $placeholders) . ") OR c.Project IS NULL OR TRIM(c.Project) = '')";
                } else if (!empty($projectValues)) {
                    $placeholders = [];
                    foreach ($projectValues as $key => $project) {
                        $paramName = ":project" . $key;
                        $placeholders[] = $paramName;
                        $params[$paramName] = $project;
                    }

                    $query .= " AND c.Project IN (" . implode(", ", $placeholders) . ")";
                } else if ($hasNA) {
                    $query .= " AND (c.Project IS NULL OR TRIM(c.Project) = '')";
                }
            }

            // Adicionar mais filtros conforme necessário (crop, etc.)

            // Executar a consulta
            try {
                print_r($query);
                $stmt = $this->conn->prepare($query);

                // Bind parameters
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }

                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Se existem registros para esta categoria com os filtros atuais, adicionar à lista
                if ($result && $result['count'] > 0) {
                    $availableCategories[$categoryKey] = [
                        'name' => $categoryName,
                        'count' => $result['count']
                    ];
                }
            } catch (PDOException $e) {
                // Log do erro
                error_log("Erro ao verificar disponibilidade da categoria {$categoryKey}: " . $e->getMessage());
            }
        }

        print_r($availableCategories);
        return $availableCategories;
    }

    /**
     * Get available gt params
     * @return array List of available parameters
     */
    public function getAvailableGtParams($param)
    {
        if ($param == 'xrf') {
            $query = "SELECT DISTINCT $param.Molecule FROM $param ORDER BY $param.Molecule";
        } else {
            $query = "SELECT DISTINCT $param.Parameter FROM $param ORDER BY $param.Parameter";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $params = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($param == 'xrf') {
                $params[] = $row["Molecule"];
            } else {
                $params[] = $row["Parameter"];
            }
        }

        return $params;
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
        $lab_measures = $this->getAvailableGtParams('lab_measures');
        $ecophysio = $this->getAvailableGtParams('ecophysio');
        $xrf = $this->getAvailableGtParams('xrf');
        $hormones = $this->getAvailableGtParams('hormones');
        $genes = $this->getAvailableGtParams('genes');

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
                c.ID,
                c.Date,
                YEAR(c.Date) as Year,
                c.Code_field,
                c.FW_sample,
                c.Crop,
                c.Cultivar,
                c.Irrigation,
                c.Test_site,
                c.Project,
                c.Coord,
                s.Spectra_device,
                s.Morphology,
                s.Local,
        ";

        if (isset($filters['data_categories']) && !empty($filters['data_categories'])) {
            foreach ($filters['data_categories'] as $category) {
                $pivotColumns = [];
                if ($category == 'lab_measures') {
                    foreach ($lab_measures as $measure) {
                        $columnName = "" . str_replace(' ', '_', $measure);
                        $columnName = "" . str_replace('(', '_', $columnName);
                        $columnName = "" . str_replace(')', '_', $columnName);
                        $columnName = "" . str_replace('/', '_', $columnName);
                        $columnName = "" . str_replace('º', '_', $columnName);
                        $columnName = "" . str_replace('+', '_', $columnName);
                        $columnName = "" . str_replace('%', '_', $columnName);
                        $columnName = "" . str_replace('.', '_', $columnName);
                        $columnName = "" . str_replace('=', '_', $columnName);
                        $columnName = "" . str_replace('-1', '_', $columnName);
                        $pivotColumns[] = "MAX(CASE WHEN lab_measures.Parameter = " . $this->conn->quote($measure) . " THEN lab_measures.Value ELSE NULL END) AS " . $columnName;
                    }
                    // Adicionar as colunas pivot à consulta
                    $query .= implode(",\n                ", $pivotColumns);
                    $query .= ",\n";
                } else if ($category == 'ecophysio') {
                    foreach ($ecophysio as $measure) {
                        $columnName = "" . str_replace(' ', '_', $measure);
                        $columnName = "" . str_replace('(', '_', $columnName);
                        $columnName = "" . str_replace(')', '_', $columnName);
                        $columnName = "" . str_replace('/', '_', $columnName);
                        $columnName = "" . str_replace('º', '_', $columnName);
                        $columnName = "" . str_replace('+', '_', $columnName);
                        $columnName = "" . str_replace('%', '_', $columnName);
                        $columnName = "" . str_replace('.', '_', $columnName);
                        $columnName = "" . str_replace('=', '_', $columnName);
                        $columnName = "" . str_replace('-1', '_', $columnName);
                        $columnName = "" . str_replace('\'', '_', $columnName);
                        $pivotColumns[] = "MAX(CASE WHEN ecophysio.Parameter = " . $this->conn->quote($measure) . " THEN ecophysio.Value ELSE NULL END) AS " . $columnName;
                    }
                    // Adicionar as colunas pivot à consulta
                    $query .= implode(",\n                ", $pivotColumns);
                    $query .= ",\n";
                } else if ($category == 'xrf') {
                    foreach ($xrf as $measure) {
                        $columnName = "" . str_replace(' ', '_', $measure);
                        $columnName = "" . str_replace('(', '_', $columnName);
                        $columnName = "" . str_replace(')', '_', $columnName);
                        $columnName = "" . str_replace('/', '_', $columnName);
                        $columnName = "" . str_replace('º', '_', $columnName);
                        $columnName = "" . str_replace('+', '_', $columnName);
                        $columnName = "" . str_replace('%', '_', $columnName);
                        $columnName = "" . str_replace('.', '_', $columnName);
                        $columnName = "" . str_replace('=', '_', $columnName);
                        $columnName = "" . str_replace('-1', '_', $columnName);
                        $columnName = "" . str_replace('\'', '_', $columnName);
                        $pivotColumns[] = "MAX(CASE WHEN xrf.Molecule = " . $this->conn->quote($measure) . " THEN xrf.Value ELSE NULL END) AS " . $columnName . "_" . 
                                          ", MAX(CASE WHEN xrf.Molecule = " . $this->conn->quote($measure) . " THEN xrf.Error ELSE NULL END) AS " . $columnName . "_Error";
                    }
                    // Adicionar as colunas pivot à consulta
                    $query .= implode(",\n                ", $pivotColumns);
                    $query .= ",\n";
                } else if ($category == 'hormones') {
                    foreach ($hormones as $measure) {
                        $columnName = "" . str_replace(' ', '_', $measure);
                        $columnName = "" . str_replace('(', '_', $columnName);
                        $columnName = "" . str_replace(')', '_', $columnName);
                        $columnName = "" . str_replace('/', '_', $columnName);
                        $columnName = "" . str_replace('º', '_', $columnName);
                        $columnName = "" . str_replace('+', '_', $columnName);
                        $columnName = "" . str_replace('%', '_', $columnName);
                        $columnName = "" . str_replace('.', '_', $columnName);
                        $columnName = "" . str_replace('=', '_', $columnName);
                        $columnName = "" . str_replace('-1', '_', $columnName);
                        $columnName = "" . str_replace('\'', '_', $columnName);
                        $pivotColumns[] = "MAX(CASE WHEN hormones.Parameter = " . $this->conn->quote($measure) . " THEN hormones.Value ELSE NULL END) AS " . $columnName . 
                                          ", MAX(CASE WHEN hormones.Parameter = " . $this->conn->quote($measure) . " THEN hormones.Laboratory ELSE NULL END) AS " . $columnName . "_Laboratory";
                    }
                    // Adicionar as colunas pivot à consulta
                    $query .= implode(",\n                ", $pivotColumns);
                    $query .= ",\n";
                } else if ($category == 'genes') {
                    foreach ($genes as $measure) {
                        $columnName = "" . str_replace(' ', '_', $measure);
                        $columnName = "" . str_replace('(', '_', $columnName);
                        $columnName = "" . str_replace(')', '_', $columnName);
                        $columnName = "" . str_replace('/', '_', $columnName);
                        $columnName = "" . str_replace('º', '_', $columnName);
                        $columnName = "" . str_replace('+', '_', $columnName);
                        $columnName = "" . str_replace('%', '_', $columnName);
                        $columnName = "" . str_replace('.', '_', $columnName);
                        $columnName = "" . str_replace('=', '_', $columnName);
                        $columnName = "" . str_replace('-1', '_', $columnName);
                        $columnName = "" . str_replace('\'', '_', $columnName);
                        $pivotColumns[] = "MAX(CASE WHEN genes.Parameter = " . $this->conn->quote($measure) . " THEN genes.Value ELSE NULL END) AS " . $columnName . 
                                          ", MAX(CASE WHEN genes.Parameter = " . $this->conn->quote($measure) . " THEN genes.Laboratory ELSE NULL END) AS " . $columnName . "_Laboratory";
                    }
                    // Adicionar as colunas pivot à consulta
                    $query .= implode(",\n                ", $pivotColumns);
                    $query .= ",\n";
                }
            }
        }

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
                cropid c
                JOIN spectra s ON s.ID = c.ID ";

        if (isset($filters['data_categories']) && !empty($filters['data_categories'])) {
            foreach ($filters['data_categories'] as $category) {
            $query .= "
                LEFT JOIN $category ON c.ID = $category.ID";
            }
        }

        $query .= "
            WHERE 
                1=1
        ";

        $params = [];

        // Adicionar filtros
        // Filtro de dispositivo espectral
        if (isset($filters['spectra_device']) && !empty($filters['spectra_device'])) {
            $query .= " AND s.Spectra_device = :device";
            $params[':device'] = $filters['spectra_device'];
        } else {
            $query .= " AND s.Spectra_device = NULL";
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
            $hasNA = false;
            $yearValues = [];

            // Separar o caso especial 0 (N/A) dos anos normais
            foreach ($filters['years'] as $year) {
                if ($year == 0) {
                    $hasNA = true;
                } else {
                    $yearValues[] = $year;
                }
            }

            // Se temos anos normais e N/A, usamos uma condição composta
            if (!empty($yearValues) && $hasNA) {
                $placeholders = [];
                foreach ($yearValues as $key => $year) {
                    $paramName = ":year" . $key;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $year;
                }

                $query .= " AND (YEAR(c.Date) IN (" . implode(", ", $placeholders) . ") OR c.Date IS NULL OR YEAR(c.Date) = 0)";
            }
            // Se temos apenas anos normais
            else if (!empty($yearValues)) {
                $placeholders = [];
                foreach ($yearValues as $key => $year) {
                    $paramName = ":year" . $key;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $year;
                }

                $query .= " AND YEAR(c.Date) IN (" . implode(", ", $placeholders) . ")";
            }
            // Se temos apenas N/A
            else if ($hasNA) {
                $query .= " AND (c.Date IS NULL OR YEAR(c.Date) = 0)";
            }
        } else {
            $query .= " AND c.Date IS NULL";
        }

        if (isset($filters['crop_types']) && !empty($filters['crop_types'])) {
            $placeholders = [];
            foreach ($filters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $placeholders[] = $paramName;
                $params[$paramName] = $crop;
            }
            $query .= " AND c.Crop IN (" . implode(", ", $placeholders) . ")";
        } else {
            $query .= " AND c.Crop IS NULL";
        }

        if (isset($filters['projects']) && !empty($filters['projects'])) {
            $hasNA = false;
            $projectValues = [];

            // Separar o caso especial 'N/A' dos projetos normais
            foreach ($filters['projects'] as $project) {
                if ($project === 'N/A') {
                    $hasNA = true;
                } else {
                    $projectValues[] = $project;
                }
            }

            // Construir a cláusula SQL apropriada
            if (!empty($projectValues) && $hasNA) {
                // Caso com projetos normais e N/A
                $placeholders = [];
                foreach ($projectValues as $key => $project) {
                    $paramName = ":project" . $key;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $project;
                }

                $query .= " AND (c.Project IN (" . implode(", ", $placeholders) . ") OR c.Project IS NULL OR TRIM(c.Project) = '')";
            } else if (!empty($projectValues)) {
                // Caso apenas com projetos normais
                $placeholders = [];
                foreach ($projectValues as $key => $project) {
                    $paramName = ":project" . $key;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $project;
                }

                $query .= " AND c.Project IN (" . implode(", ", $placeholders) . ")";
            } else if ($hasNA) {
                // Caso apenas com N/A
                $query .= " AND (c.Project IS NULL OR TRIM(c.Project) = '')";
            }
        } else {
            $query .= " AND c.Project IS NULL";
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
            c.Date,
            YEAR(c.Date) as Year,
            c.Code_field,
            c.FW_sample,
            c.Crop,
            c.Cultivar,
            c.Irrigation,
            c.Test_site,
            c.Project,
            c.Coord,
            s.Spectra_device,
            s.Morphology,
            s.Local,
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
            $hasNA = false;
            $yearValues = [];

            // Separar o caso especial 0 (N/A) dos anos normais
            foreach ($filters['years'] as $year) {
                if ($year == 0) {
                    $hasNA = true;
                } else {
                    $yearValues[] = $year;
                }
            }

            // Se temos anos normais e N/A, usamos uma condição composta
            if (!empty($yearValues) && $hasNA) {
                $yearPlaceholders = array_fill(0, count($yearValues), '?');
                $query .= " AND (YEAR(c.Date) IN (" . implode(", ", $yearPlaceholders) . ") OR c.Date IS NULL OR YEAR(c.Date) = 0)";
            }
            // Se temos apenas anos normais
            else if (!empty($yearValues)) {
                $yearPlaceholders = array_fill(0, count($yearValues), '?');
                $query .= " AND YEAR(c.Date) IN (" . implode(", ", $yearPlaceholders) . ")";
            }
            // Se temos apenas N/A
            else if ($hasNA) {
                $query .= " AND (c.Date IS NULL OR YEAR(c.Date) = 0)";
            }
        }

        if (isset($filters['crop_types']) && !empty($filters['crop_types'])) {
            $placeholders = [];
            foreach ($filters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $placeholders[] = $paramName;
            }
            $query .= " AND c.Crop IN (" . implode(", ", $placeholders) . ")";
        }

        if (isset($filters['projects']) && !empty($filters['projects'])) {
            $hasNA = false;
            $projectValues = [];

            // Separar o caso especial 'N/A' dos projetos normais
            foreach ($filters['projects'] as $project) {
                if ($project === 'N/A') {
                    $hasNA = true;
                } else {
                    $projectValues[] = $project;
                }
            }

            // Construir a cláusula SQL apropriada
            if (!empty($projectValues) && $hasNA) {
                // Caso com projetos normais e N/A
                $projectPlaceholders = array_fill(0, count($projectValues), '?');
                $query .= " AND (c.Project IN (" . implode(", ", $projectPlaceholders) . ") OR c.Project IS NULL OR TRIM(c.Project) = '')";
            } else if (!empty($projectValues)) {
                // Caso apenas com projetos normais
                $projectPlaceholders = array_fill(0, count($projectValues), '?');
                $query .= " AND c.Project IN (" . implode(", ", $projectPlaceholders) . ")";
            } else if ($hasNA) {
                // Caso apenas com N/A
                $query .= " AND (c.Project IS NULL OR TRIM(c.Project) = '')";
            }
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
            $yearValues = [];

            // Filtrar apenas os anos não-zero para binding
            foreach ($filters['years'] as $year) {
                if ($year != 0) {
                    $yearValues[] = $year;
                }
            }

            // Só fazer binding para os anos normais
            if (!empty($yearValues)) {
                foreach ($yearValues as $year) {
                    $stmt->bindValue($paramIndex++, $year);
                }
            }
        }

        if (isset($filters['crop_types']) && !empty($filters['crop_types'])) {
            foreach ($filters['crop_types'] as $key => $crop) {
                $paramName = ":crop" . $key;
                $stmt->bindValue($paramName, $crop);
            }
        }

        if (isset($filters['projects']) && !empty($filters['projects'])) {
            $projectValues = [];

            // Filtrar apenas projetos não-N/A para binding
            foreach ($filters['projects'] as $project) {
                if ($project !== 'N/A') {
                    $projectValues[] = $project;
                }
            }

            // Só fazer binding para os projetos normais
            if (!empty($projectValues)) {
                foreach ($projectValues as $project) {
                    $stmt->bindValue($paramIndex++, $project);
                }
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
