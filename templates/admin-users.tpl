{* templates/admin-users.tpl *}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - {$smarty.const.APP_NAME}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            {* Sidebar *}
            <div class="col-md-3 sidebar">
                <h4 class="sidebar-header">ADMIN PANEL</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {if basename($_SERVER['PHP_SELF']) == 'admin-users.php'}active{/if}" href="admin-users.php">
                            <i class="bi bi-people"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if basename($_SERVER['PHP_SELF']) == 'admin-projects.php'}active{/if}" href="admin-projects.php">
                            <i class="bi bi-folder"></i> Project Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {if basename($_SERVER['PHP_SELF']) == 'admin-activity-log.php'}active{/if}" href="admin-activity-log.php">
                            <i class="bi bi-clipboard-data"></i> Activity Log
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-arrow-left"></i> Back to Main
                        </a>
                    </li>
                </ul>
            </div>

            {* Main Content *}
            <div class="col-md-9 main-content">
                <div class="user-info mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Welcome, {$current_user.username}</strong>
                        <span class="badge bg-success">Admin</span>
                    </div>
                    <form method="post" action="index.php" class="d-inline">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            Logout
                        </button>
                    </form>
                </div>

                <h2>User Management</h2>

                {* Error/Success Messages *}
                {if $error}
                    <div class="alert alert-danger">
                        {$error}
                    </div>
                {/if}

                {if $success}
                    <div class="alert alert-success">
                        {$success}
                    </div>
                {/if}

                <div class="row">
                    {* Create User Form *}
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                Create New User
                            </div>
                            <div class="card-body">
                                <form method="post" action="admin-users.php">
                                    <input type="hidden" name="action" value="create_user">

                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="project" class="form-label">Projects (comma-separated)</label>
                                        <input type="text" class="form-control" id="project" name="project"
                                            placeholder="Enter projects separated by commas" required
                                            pattern="^[a-zA-Z0-9\s,-]+$"
                                            title="Enter projects separated by commas (max 5 projects)">
                                        <small class="form-text text-muted">
                                            Enter up to 5 projects, separated by commas (e.g., Project1, Project2)
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-select" id="role" name="role">
                                            <option value="user" selected>User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Create User</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {* User List *}
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                User List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Project</th>
                                                <th>Role</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach from=$users item=user}
                                                <tr>
                                                    <td>{$user.id}</td>
                                                    <td>{$user.username}</td>
                                                    <td>{$user.email}</td>
                                                    <td>
                                                        {* Formulário de atualização de projetos *}
                                                        <form method="post" action="admin-users.php" class="d-inline">
                                                            <input type="hidden" name="action" value="update_project">
                                                            <input type="hidden" name="user_id" value="{$user.id}">
                                                            <input type="text" name="project"
                                                                class="form-control form-control-sm" value="{$user.project}"
                                                                placeholder="Enter projects (comma-separated)"
                                                                onchange="this.form.submit()">
                                                        </form>
                                                    </td>
                                                    <td>{$user.role}</td>
                                                    <td>
                                                        {* Delete User Form *}
                                                        {if $user.id != $current_user.id}
                                                            <form method="post" action="admin-users.php"
                                                                onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                                <input type="hidden" name="action" value="delete_user">
                                                                <input type="hidden" name="user_id" value="{$user.id}">
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        {else}
                                                            <span class="text-muted">Current User</span>
                                                        {/if}
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* Additional JavaScript for interactivity *}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password validation for registration form
            const registerForm = document.querySelector('#register form');
            const passwordInput = registerForm.querySelector('#register_password');
            const emailInput = registerForm.querySelector('#register_email');

            // Password strength indicators
            const passwordStrengthIndicator = document.createElement('div');
            passwordStrengthIndicator.className = 'password-strength-indicator mt-2 small';
            passwordInput.parentNode.insertBefore(passwordStrengthIndicator, passwordInput.nextSibling);

            // Email validation
            emailInput.addEventListener('input', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.value)) {
                    this.setCustomValidity('Please enter a valid email address');
                } else {
                    this.setCustomValidity('');
                }
            });

            // Password strength check
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedbackText = '';
                let feedbackClass = '';

                // Check length
                if (password.length >= 8) {
                    strength++;
                }

                // Check for uppercase
                if (/[A-Z]/.test(password)) {
                    strength++;
                }

                // Check for lowercase
                if (/[a-z]/.test(password)) {
                    strength++;
                }

                // Check for numbers
                if (/[0-9]/.test(password)) {
                    strength++;
                }

                // Check for special characters
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                    strength++;
                }

                // Provide feedback based on strength
                switch (strength) {
                    case 0:
                    case 1:
                        feedbackText = 'Very Weak';
                        feedbackClass = 'text-danger';
                        break;
                    case 2:
                    case 3:
                        feedbackText = 'Weak';
                        feedbackClass = 'text-warning';
                        break;
                    case 4:
                        feedbackText = 'Medium';
                        feedbackClass = 'text-info';
                        break;
                    case 5:
                        feedbackText = 'Strong';
                        feedbackClass = 'text-success';
                        break;
                }

                // Update the strength indicator
                passwordStrengthIndicator.innerHTML = 'Password Strength: <span class="' +
                    feedbackClass + '">' + feedbackText + '</span>';

                // Set custom validity
                if (strength < 3) {
                    this.setCustomValidity(
                        'Password is too weak. Include uppercase, lowercase, numbers, and special characters.'
                        );
                } else {
                    this.setCustomValidity('');
                }
            });

            // Prevent form submission if validation fails
            registerForm.addEventListener('submit', function(e) {
                if (!registerForm.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                registerForm.classList.add('was-validated');
            }, false);
        });
    </script>
</body>

</html>