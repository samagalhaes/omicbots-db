<!-- 

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    project VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users 
ADD COLUMN failed_attempts INT DEFAULT 0,
ADD COLUMN last_attempt_time TIMESTAMP NULL,
ADD COLUMN reset_token VARCHAR(64) NULL,
ADD COLUMN reset_token_expiry DATETIME NULL;

-->

<?php
class User
{
    private $conn;
    private $table_name = 'users';

    // User properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $project;
    public $role;
    public $created_at;

    // Constructor
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Login method
    public function login()
    {
        $query = "SELECT id, username, email, password, project, role 
                  FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($this->password, $row['password'])) {
            // Set user properties
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->project = $row['project'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // Register method
    public function register()
    {
        // Check if username or email already exists
        $check_query = "SELECT * FROM " . $this->table_name . " 
                        WHERE username = :username OR email = :email";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':username', $this->username);
        $check_stmt->bindParam(':email', $this->email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            return false; // User already exists
        }

        // Insert new user
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username = :username, 
                      email = :email, 
                      password = :password, 
                      project = :project,
                      role = :role,
                      created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':project', $this->project);
        $stmt->bindParam(':role', $this->role);

        return $stmt->execute();
    }

    // Get user projects based on role
    public function getUserProjects()
    {
        // For admin, return all projects
        if ($this->role === 'admin') {
            $query = "SELECT DISTINCT Project FROM cropid ORDER BY Project";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($this->project)) {
            return [];
        }

        return array_map('trim', explode(',', $this->project));
    }

    // Validate user access to a specific project
    /**
     * Verificar se o usuário tem acesso a um projeto específico
     * @param string $requestedProject Projeto solicitado
     * @return bool Se o usuário tem acesso
     */
    public function canAccessProject($requestedProject)
    {
        // Admin sempre tem acesso
        if ($this->role === 'admin') {
            return true;
        }

        // Se nenhum projeto definido, sem acesso
        if (empty($this->project)) {
            return false;
        }

        // Converter projetos em array
        $userProjects = array_map('trim', explode(',', $this->project));

        // Verificar se o projeto solicitado está na lista
        return in_array($requestedProject, $userProjects);
    }

    // Additional method to check and limit login attempts
    public function checkLoginAttempts()
    {
        $max_attempts = 5;
        $lockout_time = 15 * 60; // 15 minutes

        $query = "SELECT 
                failed_attempts, 
                last_attempt_time,
                (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_attempt_time)) as time_since_last_attempt
              FROM users 
              WHERE username = :username OR email = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if user is still locked out
            if (
                $user['failed_attempts'] >= $max_attempts &&
                $user['time_since_last_attempt'] < $lockout_time
            ) {
                throw new Exception("Too many login attempts. Please try again after " .
                    ceil(($lockout_time - $user['time_since_last_attempt']) / 60) . " minutes.");
            }
        }

        return true;
    }

    // Method to update login attempt tracking
    public function updateLoginAttempt($success = false)
    {
        $query = $success
            ? "UPDATE users SET failed_attempts = 0, last_attempt_time = NOW() WHERE username = :username OR email = :username"
            : "UPDATE users 
           SET failed_attempts = LEAST(failed_attempts + 1, 5), 
               last_attempt_time = NOW() 
           WHERE username = :username OR email = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();
    }

    // Enhanced login method with attempt tracking
    // public function login()
    // {
    //     try {
    //         // Check login attempts before proceeding
    //         $this->checkLoginAttempts();

    //         $query = "SELECT id, username, email, password, project, role 
    //               FROM " . $this->table_name . " 
    //               WHERE username = :username OR email = :username";

    //         $stmt = $this->conn->prepare($query);
    //         $stmt->bindParam(':username', $this->username);
    //         $stmt->execute();

    //         $row = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if ($row && password_verify($this->password, $row['password'])) {
    //             // Successful login
    //             $this->updateLoginAttempt(true);

    //             // Set user properties
    //             $this->id = $row['id'];
    //             $this->email = $row['email'];
    //             $this->project = $row['project'];
    //             $this->role = $row['role'];
    //             return true;
    //         } else {
    //             // Failed login
    //             $this->updateLoginAttempt(false);
    //             return false;
    //         }
    //     } catch (Exception $e) {
    //         // Re-throw any exceptions from login attempts
    //         throw $e;
    //     }
    // }

    // Password reset method
    public function requestPasswordReset()
    {
        // Generate a unique token
        $reset_token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $query = "UPDATE " . $this->table_name . " 
              SET reset_token = :token, 
                  reset_token_expiry = :expiry 
              WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $reset_token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':email', $this->email);

        if ($stmt->execute()) {
            // Here you would typically send an email with the reset link
            // For example: /reset-password.php?token=$reset_token
            return $reset_token;
        }

        return false;
    }

    // Validate and reset password
    public function resetPassword($reset_token, $new_password)
    {
        $query = "SELECT id FROM " . $this->table_name . " 
              WHERE reset_token = :token 
              AND reset_token_expiry > NOW()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $reset_token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password and clear reset token
            $update_query = "UPDATE " . $this->table_name . " 
                         SET password = :password, 
                             reset_token = NULL, 
                             reset_token_expiry = NULL 
                         WHERE reset_token = :token";

            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':token', $reset_token);

            return $update_stmt->execute();
        }

        return false;
    }

    /**
     * Get list of all users with enhanced security and logging
     * @param int $limit Limit number of users returned
     * @param int $offset Offset for pagination
     * @return array List of users
     */
    public function getAllUsers($limit = 0, $offset = 0)
    {
        // Verificar permissão de admin
        $this->requireAdminAccess();

        // Registrar atividade de listagem de usuários
        $this->logAdminActivity('list_users', [
            'limit' => $limit,
            'offset' => $offset
        ]);

        $query = "SELECT 
                    id, 
                    username, 
                    email, 
                    project, 
                    role, 
                    created_at,
                    failed_attempts,
                    last_attempt_time
                  FROM users";

        // Adicionar limite e offset se especificados
        if ($limit > 0) {
            $query .= " LIMIT :limit";
            if ($offset > 0) {
                $query .= " OFFSET :offset";
            }
        }

        $stmt = $this->conn->prepare($query);

        if ($limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset > 0) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Criar novo usuário pelo admin
     * @param array $userData Dados do usuário
     * @return bool Sucesso da criação
     */
    public function createUserByAdmin($userData)
    {
        // Verificar permissão de admin
        $this->requireAdminAccess();

        // Validações adicionais de segurança
        $sanitizedData = $this->sanitizeUserData($userData);

        // Validar dados
        if (
            empty($sanitizedData['username']) || empty($sanitizedData['email']) ||
            empty($sanitizedData['password']) || empty($sanitizedData['project'])
        ) {
            throw new Exception('Todos os campos são obrigatórios');
        }

        $sanitizedData['project'] = $this->normalizeProjects($sanitizedData['project']);


        // Verificar se usuário ou email já existem
        $check_query = "SELECT * FROM users WHERE username = :username OR email = :email";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':username', $sanitizedData['username']);
        $check_stmt->bindParam(':email', $sanitizedData['email']);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            throw new Exception('Usuário ou email já existem');
        }

        // Preparar query de inserção
        $query = "INSERT INTO users 
                  SET username = :username, 
                      email = :email, 
                      password = :password, 
                      project = :project,
                      role = :role,
                      created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Hash da senha
        $hashed_password = password_hash($sanitizedData['password'], PASSWORD_DEFAULT);

        // Definir role padrão se não especificado
        $role = $sanitizedData['role'] ?? 'user';

        // Bind de parâmetros
        $stmt->bindParam(':username', $sanitizedData['username']);
        $stmt->bindParam(':email', $sanitizedData['email']);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':project', $sanitizedData['project']);
        $stmt->bindParam(':role', $role);

        return $stmt->execute();
    }

    /**
     * Atualizar projeto de usuário com log de atividade
     * @param int $userId ID do usuário
     * @param string $newProject Novo projeto
     * @return bool Sucesso da atualização
     */
    public function updateUserProject($userId, $newProject)
    {
        // Verificar permissão de admin
        $this->requireAdminAccess();

        // Obter informações do usuário antes da atualização
        $user = $this->getUserById($userId);

        // Registrar atividade de atualização de projeto
        $this->logAdminActivity('update_user_project', [
            'user_id' => $userId,
            'old_project' => $user['project'],
            'new_project' => $newProject
        ]);

        $query = "UPDATE users SET project = :project WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project', $newProject);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }


    /**
     * Remover usuário com log de atividade
     * @param int $userId ID do usuário a ser removido
     * @return bool Sucesso da remoção
     */
    public function deleteUser($userId)
    {
        // Verificar permissão de admin
        $this->requireAdminAccess();

        // Prevenir remoção do próprio usuário
        if ($userId == $this->id) {
            throw new Exception('Não é possível remover o próprio usuário');
        }

        // Iniciar transação para garantir atomicidade
        $this->conn->beginTransaction();

        try {
            // Obter informações do usuário antes da remoção
            $user = $this->getUserById($userId);

            // Registrar log de atividade antes da remoção
            $this->logAdminActivity('delete_user', [
                'user_id' => $userId,
                'username' => $user['username'],
                'email' => $user['email'],
                'project' => $user['project']
            ]);

            // Remover usuário
            $query = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $result = $stmt->execute();

            // Confirmar transação
            $this->conn->commit();

            return $result;
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            $this->conn->rollBack();

            // Log do erro
            error_log("Erro ao remover usuário: " . $e->getMessage());

            // Relançar a exceção
            throw $e;
        }
    }

    /**
     * Buscar usuário por ID
     * @param int $userId ID do usuário
     * @return array Dados do usuário
     */
    public function getUserById($userId)
    {
        // // Verificar se o usuário atual é admin
        // if ($this->role !== 'admin') {
        //     throw new Exception('Apenas administradores podem buscar detalhes de usuários');
        // }

        $query = "SELECT id, username, email, project, role FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Normalizar e validar lista de projetos
     * @param string|array $projects Projetos a serem processados
     * @return string Lista de projetos formatada
     */
    private function normalizeProjects($projects)
    {
        // Se for uma string, dividir por vírgulas
        if (is_string($projects)) {
            $projects = array_map('trim', explode(',', $projects));
        }

        // Remover projetos duplicados e vazios
        $projects = array_unique(array_filter($projects));

        // Limitar número de projetos
        $projects = array_slice($projects, 0, 5);

        // Validar cada projeto
        $validProjects = [];
        foreach ($projects as $project) {
            // Remover caracteres especiais e limitar tamanho
            $cleanProject = substr(preg_replace("/[^a-zA-Z0-9\s-]/", "", $project), 0, 50);
            if (!empty($cleanProject)) {
                $validProjects[] = $cleanProject;
            }
        }

        // Retornar como string separada por vírgulas
        return implode(', ', $validProjects);
    }



    /**
     * Adicionar projeto ao usuário
     * @param string $newProject Novo projeto a ser adicionado
     * @return bool Sucesso da adição
     */
    public function addProject($newProject)
    {
        // Verificar permissão de admin
        $this->requireAdminAccess();

        // Normalizar novo projeto
        $newProject = $this->normalizeProjects($newProject);

        // Se já estiver vazio, simplesmente definir
        if (empty($this->project)) {
            $this->project = $newProject;
        } else {
            // Adicionar aos projetos existentes, evitando duplicatas
            $projects = array_map('trim', explode(',', $this->project));
            $newProjects = array_map('trim', explode(',', $newProject));

            // Mesclar e remover duplicatas
            $mergedProjects = array_unique(array_merge($projects, $newProjects));

            // Limitar para 5 projetos
            $mergedProjects = array_slice($mergedProjects, 0, 5);

            // Converter de volta para string
            $this->project = implode(', ', $mergedProjects);
        }

        // Atualizar no banco de dados
        $query = "UPDATE users SET project = :project WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project', $this->project);
        $stmt->bindParam(':user_id', $this->id);

        // Registrar log de atividade
        $this->logAdminActivity('add_user_project', [
            'user_id' => $this->id,
            'new_project' => $newProject,
            'current_projects' => $this->project
        ]);

        return $stmt->execute();
    }

    /**
     * Remover projeto do usuário
     * @param string $projectToRemove Projeto a ser removido
     * @return bool Sucesso da remoção
     */
    public function removeProject($projectToRemove)
    {
        // Verificar permissão de admin
        $this->requireAdminAccess();

        // Se não houver projetos, não há nada para remover
        if (empty($this->project)) {
            return false;
        }

        // Converter projetos para array
        $projects = array_map('trim', explode(',', $this->project));

        // Remover o projeto específico
        $projects = array_filter($projects, function ($project) use ($projectToRemove) {
            return $project !== trim($projectToRemove);
        });

        // Atualizar projetos
        $this->project = implode(', ', $projects);

        // Atualizar no banco de dados
        $query = "UPDATE users SET project = :project WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project', $this->project);
        $stmt->bindParam(':user_id', $this->id);

        // Registrar log de atividade
        $this->logAdminActivity('remove_user_project', [
            'user_id' => $this->id,
            'removed_project' => $projectToRemove,
            'remaining_projects' => $this->project
        ]);

        return $stmt->execute();
    }

    /**
     * Substituir todos os projetos do usuário
     * @param string|array $newProjects Novos projetos
     * @return bool Sucesso da atualização
     */
    public function replaceProjects($newProjects)
    {
        // // Verificar permissão de admin
        // $this->requireAdminAccess();

        // Normalizar projetos
        $normalizedProjects = $this->normalizeProjects($newProjects);

        // Atualizar no banco de dados
        $query = "UPDATE users SET project = :project WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project', $normalizedProjects);
        $stmt->bindParam(':user_id', $this->id);

        // Registrar log de atividade
        $this->logAdminActivity('replace_user_projects', [
            'user_id' => $this->id,
            'old_projects' => $this->project,
            'new_projects' => $normalizedProjects
        ]);

        // Atualizar propriedade do objeto
        $this->project = $normalizedProjects;

        return $stmt->execute();
    }

    /**
     * Validar lista de projetos
     * @param array|string $projects Projetos a serem validados
     * @return array Projetos válidos
     * @throws Exception Se projetos forem inválidos
     */
    public function validateProjects($projects)
    {
        // Converter para array se for string
        if (is_string($projects)) {
            $projects = array_map('trim', explode(',', $projects));
        }

        // Remover projetos duplicados e vazios
        $projects = array_unique(array_filter($projects));

        // Limitar número de projetos
        if (count($projects) > 5) {
            throw new Exception('Máximo de 5 projetos permitidos');
        }

        // Obter lista de projetos disponíveis
        $availableProjects = $this->getProjects();

        // Validar cada projeto
        $invalidProjects = array_diff($projects, $availableProjects);
        if (!empty($invalidProjects)) {
            throw new Exception('Projetos inválidos: ' . implode(', ', $invalidProjects));
        }

        return $projects;
    }

    /**
     * Adicionar um novo projeto à lista de projetos disponíveis
     * @param string $newProject Novo projeto a ser adicionado
     * @param User $adminUser Usuário admin que está adicionando o projeto
     * @return bool Sucesso da adição
     */
    public function addNewProject($newProject, $adminUser)
    {
        // Verificar se o usuário é admin
        if ($adminUser->role !== 'admin') {
            throw new Exception('Apenas administradores podem adicionar novos projetos');
        }

        // Limpar e validar o nome do projeto
        $cleanProject = trim(preg_replace("/[^a-zA-Z0-9\s-]/", "", $newProject));

        if (empty($cleanProject)) {
            throw new Exception('Nome de projeto inválido');
        }

        // Verificar se o projeto já existe
        $query = "SELECT COUNT(*) FROM cropid WHERE Project = :project";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project', $cleanProject);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Projeto já existe');
        }

        // Adicionar projeto à tabela de cropid
        $insertQuery = "INSERT INTO cropid (Project) VALUES (:project)";
        $insertStmt = $this->conn->prepare($insertQuery);
        $insertStmt->bindParam(':project', $cleanProject);

        // Registrar log de atividade
        $adminUser->logAdminActivity('add_new_project', [
            'project_name' => $cleanProject
        ]);

        return $insertStmt->execute();
    }

    





    /**
     * Verifica se o usuário atual tem permissão de admin
     * @throws Exception Se o usuário não for admin
     */
    private function requireAdminAccess()
    {
        if ($this->role !== 'admin') {
            print_r($this->role);
            print_r($this->username);
            // Log de tentativa de acesso não autorizado
            error_log("Tentativa não autorizada de acesso admin por usuário: " . $this->username);

            throw new Exception('Acesso negado. Requer privilégios de administrador.');
        }
    }

    /**
     * Método para registrar atividades administrativas
     * @param string $action Ação realizada
     * @param array $details Detalhes da ação
     */
    private function logAdminActivity($action, $details = [])
    {
        try {
            $log_query = "INSERT INTO admin_activity_log 
                          (admin_id, admin_username, action, details, created_at) 
                          VALUES (:admin_id, :admin_username, :action, :details, NOW())";

            $stmt = $this->conn->prepare($log_query);
            $stmt->bindValue(':admin_id', $this->id);
            $stmt->bindValue(':admin_username', $this->username);
            $stmt->bindValue(':action', $action);
            $stmt->bindValue(':details', json_encode($details));

            $stmt->execute();
        } catch (Exception $e) {
            // Log de erro de gravação de log
            error_log("Erro ao registrar atividade admin: " . $e->getMessage());
        }
    }


    /**
     * Sanitizar dados do usuário
     * @param array $userData Dados originais do usuário
     * @return array Dados sanitizados
     */
    private function sanitizeUserData($userData)
    {
        $sanitized = [];

        // Sanitização de username
        $sanitized['username'] = filter_var(trim($userData['username']), FILTER_SANITIZE_STRING);
        if (empty($sanitized['username'])) {
            throw new Exception('Nome de usuário inválido');
        }

        // Sanitização de email
        $sanitized['email'] = filter_var(trim($userData['email']), FILTER_SANITIZE_EMAIL);
        if (!filter_var($sanitized['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Endereço de email inválido');
        }

        // Validação de senha
        if (strlen($userData['password']) < PASSWORD_MIN_LENGTH) {
            throw new Exception('Senha muito curta. Mínimo de ' . PASSWORD_MIN_LENGTH . ' caracteres.');
        }
        $sanitized['password'] = $userData['password'];

        // Validação de projeto
        $sanitized['project'] = filter_var(trim($userData['project']), FILTER_SANITIZE_STRING);
        if (empty($sanitized['project'])) {
            throw new Exception('Projeto inválido');
        }

        // Validação de role
        $sanitized['role'] = in_array($userData['role'], ['user', 'admin'])
            ? $userData['role']
            : 'user';

        return $sanitized;
    }
}
?>