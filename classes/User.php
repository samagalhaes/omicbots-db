<!-- 

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    project VARCHAR(100) NOT NULL,
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

        // For regular users, return their assigned project
        return [$this->project];
    }

    // Validate user access to a specific project
    public function canAccessProject($project)
    {
        // Admin can access all projects
        if ($this->role === 'admin') {
            return true;
        }

        // Regular user can only access their assigned project
        return $this->project === $project;
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
}
?>