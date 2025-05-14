{* templates/sql-query.tpl *}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Query Executor - {$smarty.const.APP_NAME}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css" rel="stylesheet">
    <style>
        .CodeMirror {
            height: 300px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .result-table {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
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
                        <a class="nav-link active" href="sql-query.php">SQL Query</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Back to Main</a>
                    </li>
                </ul>

                {* Database List *}
                <div class="mt-4">
                    <h5 class="sidebar-header">Databases</h5>
                    <div class="list-group">
                        {foreach from=$databases item=database}
                            <a href="#" class="list-group-item list-group-item-action database-selector">
                                {$database}
                            </a>
                        {/foreach}
                    </div>
                </div>
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

                <h2>SQL Query Executor</h2>

                {* Error Messages *}
                {if $sql_error}
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error:</strong> {$sql_error}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                {/if}

                {* Query Form *}
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="post" action="sql-query.php" id="sqlQueryForm">
                            <div class="mb-3">
                                <label for="sql_query" class="form-label">SQL Query</label>
                                <textarea 
                                    class="form-control" 
                                    id="sql_query" 
                                    name="sql_query" 
                                    rows="5"
                                    placeholder="Enter your SQL query here (SELECT only)"
                                >{if isset($_POST.sql_query)}{$_POST.sql_query}{/if}</textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        Execute Query
                                    </button>
                                    <button type="button" class="btn btn-secondary ms-2" id="clearQueryBtn">
                                        Clear
                                    </button>
                                </div>
                                {if $execution_time > 0}
                                    <div class="text-muted">
                                        Execution Time: {$execution_time} seconds
                                        {if $affected_rows > 0}
                                            | Affected Rows: {$affected_rows}
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        </form>
                    </div>
                </div>

                {* Query Results *}
                {if $sql_result}
                    <div class="card">
                        <div class="card-header">
                            Query Results
                        </div>
                        <div class="card-body result-table">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            {foreach from=$sql_result[0] key=column item=value}
                                                <th>{$column}</th>
                                            {/foreach}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach from=$sql_result item=row}
                                            <tr>
                                                {foreach from=$row item=value}
                                                    <td>
                                                        {if $value === null}
                                                            <span class="text-muted">NULL</span>
                                                        {else}
                                                            {$value}
                                                        {/if}
                                                    </td>
                                                {/foreach}
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            Total Results: {$sql_result|count}
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>

    {* Scripts *}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/sql/sql.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar CodeMirror
            const editor = CodeMirror.fromTextArea(document.getElementById('sql_query'), {
                mode: 'text/x-sql',
                theme: 'monokai',
                lineNumbers: true,
                indentWithTabs: true,
                smartIndent: true,
                autofocus: true
            });

            // Botão de limpar
            document.getElementById('clearQueryBtn').addEventListener('click', function() {
                editor.setValue('');
                editor.focus();
            });

            // Seletor de banco de dados
            const databaseSelectors = document.querySelectorAll('.database-selector');
            databaseSelectors.forEach(selector => {
                selector.addEventListener('click', function(e) {
                    e.preventDefault();
                    const database = this.textContent.trim();
                    
                    // Adicionar USE statement no início da consulta
                    const currentQuery = editor.getValue();
                    const useStatement = 'USE ' + database + ';\n';
                    
                    // Adicionar USE no início se já não existir
                    if (!currentQuery.trim().toUpperCase().startsWith('USE')) {
                        editor.setValue(useStatement + currentQuery);
                    }
                });
            });

            // Validação de formulário
            // document.getElementById('sqlQueryForm').addEventListener('submit', function(e) {
            //     const query = editor.getValue().trim();
            //     const allowedQueryTypes = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'USE', 'TRUNCATE', 'INSERT', 'UPDATE', 'DELETE'];
            //     const firstWord = query.split(/\s+/)[0].toUpperCase();

            //     if (!allowedQueryTypes.includes(firstWord)) {
            //         e.preventDefault();
            //         alert('Only SELECT, SHOW, DESCRIBE, and EXPLAIN queries are allowed.');
            //     }
            // });
        });
    </script>
</body>
</html>