{* templates/index.tpl - Template principal Smarty *}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$smarty.const.APP_NAME}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        {include file="partials/header.tpl"}
        
        {if $message}
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {$message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}
        
        {* Exibir mensagens flash se houver *}
        {if $smarty.session.flash_message}
            <div class="alert alert-{$smarty.session.flash_message.type} alert-dismissible fade show" role="alert">
                {$smarty.session.flash_message.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}
        
        <div class="filter-section">
            {include file="partials/filter_form.tpl"}
        </div>
        
        {if $data}
            <div class="download-section">
                {include file="partials/download_form.tpl"}
            </div>
            
            <div class="results-table">
                {include file="partials/data_table.tpl"}
            </div>
        {else}
            <div class="card my-4">
                <div class="card-body text-center">
                    <i class="bi bi-info-circle fs-1 text-info mb-3"></i>
                    <h4>Selecione os filtros e clique em "Buscar Dados"</h4>
                    <p class="text-muted">O resultado da sua consulta será exibido aqui.</p>
                </div>
            </div>
        {/if}
        
        {include file="partials/footer.tpl"}
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>