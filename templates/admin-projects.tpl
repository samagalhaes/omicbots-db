{* templates/admin-projects.tpl *}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - {$smarty.const.APP_NAME}</title>
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
                        <a class="nav-link" href="admin-users.php">User Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin-projects.php">Project Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-activity-log.php">Activity Log</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Back to Main</a>
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

                <h2>Project Management</h2>

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
                    {* Add New Project *}
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                Add New Project
                            </div>
                            <div class="card-body">
                                <form method="post" action="admin-projects.php">
                                    <input type="hidden" name="action" value="add_project">
                                    
                                    <div class="mb-3">
                                        <label for="new_project" class="form-label">Project Name</label>
                                        <input type="text" class="form-control" id="new_project" name="new_project" 
                                               required 
                                               pattern="[a-zA-Z0-9\s-]+"
                                               title="Project name can only contain letters, numbers, spaces, and hyphens"
                                               placeholder="Enter new project name">
                                        <small class="form-text text-muted">
                                            Allowed characters: letters, numbers, spaces, and hyphens
                                        </small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Add Project</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {* Project List *}
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                Existing Projects
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Project Name</th>
                                            <th>Users</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$projects item=project}
                                            <tr>
                                                <td>{$project}</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {$project_stats.$project.user_count} users
                                                    </span>
                                                </td>
                                                <td>
                                                    {* Formulário de remoção de projeto *}
                                                    <form method="post" action="admin-projects.php" 
                                                          onsubmit="return confirm('Are you sure you want to remove this project? This action cannot be undone.');">
                                                        <input type="hidden" name="action" value="remove_project">
                                                        <input type="hidden" name="project" value="{$project}">
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                {if $project_stats.$project.user_count > 0}disabled title="Cannot remove project with associated users"{/if}>
                                                            <i class="bi bi-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        {foreachelse}
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    No projects found
                                                </td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>

                            {* Estatísticas Gerais *}
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Total Projects:</strong> 
                                        <span class="badge bg-primary">{$projects|count}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Total Users:</strong> 
                                        <span class="badge bg-success">
                                            {array_sum(array_column($project_stats, 'user_count'))}
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Avg Users per Project:</strong> 
                                        <span class="badge bg-secondary">
                                            {if $projects|count > 0}
                                                {round(array_sum(array_column($project_stats, 'user_count')) / $projects|count, 2)}
                                            {else}0{/if}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {* Project Management Tips *}
                        <div class="card mt-3">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> Project Management Tips
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success"></i> 
                                        Projects can be associated with multiple users
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success"></i> 
                                        A project cannot be removed if it has associated users
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success"></i> 
                                        Project names must be unique
                                    </li>
                                    <li>
                                        <i class="bi bi-info-circle text-warning"></i> 
                                        Removing a project is a permanent action
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* JavaScript *}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validação do nome do projeto em tempo real
            const newProjectInput = document.getElementById('new_project');
            
            newProjectInput.addEventListener('input', function() {
                // Remover caracteres inválidos
                this.value = this.value.replace(/[^a-zA-Z0-9\s-]/g, '');
            });

            newProjectInput.addEventListener('invalid', function(e) {
                e.target.setCustomValidity('Project name can only contain letters, numbers, spaces, and hyphens');
            });

            newProjectInput.addEventListener('input', function(e) {
                e.target.setCustomValidity('');
            });
        });
    </script>
</body>
</html>