{* templates/upload-sql.tpl *}

<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>SQL File Upload - {$smarty.const.APP_NAME}</title> <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"> <link rel="stylesheet" href="css/styles.css"> <style> .upload-progress { display: none; margin-top: 20px; } </style> </head> <body> <div class="container-fluid"> <div class="row"> {* Sidebar *} <div class="col-md-3 sidebar"> <h4 class="sidebar-header">ADMIN PANEL</h4> <ul class="nav flex-column"> <li class="nav-item"> <a class="nav-link" href="admin-users.php">User Management</a> </li> <li class="nav-item"> <a class="nav-link active" href="upload-sql.php">SQL Upload</a> </li> <li class="nav-item"> <a class="nav-link" href="index.php">Back to Main</a> </li> </ul> </div>
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

            <h2>SQL File Upload</h2>

            {* Error/Success Messages *}
            {if $error}
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {$error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            {/if}

            {if $success}
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {$success}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            {/if}

            <div class="card">
                <div class="card-header">
                    Upload SQL File
                </div>
                <div class="card-body">
                    <form id="sqlUploadForm" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="sql_file" class="form-label">
                                SQL File 
                                <small class="text-muted">(Max {$max_file_size} MB)</small>
                            </label>
                            <input 
                                class="form-control" 
                                type="file" 
                                id="sql_file" 
                                name="sql_file" 
                                accept=".sql"
                                required
                            >
                            <div class="form-text text-muted">
                                Only .sql files are allowed. Maximum file size is {$max_file_size} MB.
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="confirm_upload" required>
                            <label class="form-check-label" for="confirm_upload">
                                I understand that this operation may take a long time and might temporarily impact database performance
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="uploadButton">
                                Upload and Process SQL File
                            </button>
                        </div>
                    </form>

                    {* Progress Bar *}
                    <div class="upload-progress mt-3">
