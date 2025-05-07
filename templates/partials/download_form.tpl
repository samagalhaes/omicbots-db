{* templates/partials/download_form.tpl - Formulário de download *}
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h3 class="h5 mb-0"><i class="bi bi-download"></i> Download dos Resultados</h3>
    </div>
    <div class="card-body">
        <form method="post" action="">
            {* Manter os filtros aplicados *}
            {if $post.categories}
                {foreach from=$post.categories item=category}
                    <input type="hidden" name="categories[]" value="{$category}">
                {/foreach}
            {/if}
            
            {if $post.crop}
                <input type="hidden" name="crop" value="{$post.crop}">
            {/if}
            
            {if $post.project}
                <input type="hidden" name="project" value="{$post.project}">
            {/if}
            
            {if $post.date_from}
                <input type="hidden" name="date_from" value="{$post.date_from}">
            {/if}
            
            {if $post.date_to}
                <input type="hidden" name="date_to" value="{$post.date_to}">
            {/if}
            
            {if $post.limit}
                <input type="hidden" name="limit" value="{$post.limit}">
            {/if}
            
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label for="format" class="form-label fw-bold">Formato de Exportação:</label>
                    <select class="form-select" id="format" name="format">
                        <option value="CSV">CSV (valores separados por vírgula)</option>
                        <option value="XLSX">Excel (XLSX)</option>
                    </select>
                    <small class="text-muted">Escolha o formato para exportar os {$totalRecords} registros encontrados.</small>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" name="download" value="1" class="btn btn-success">
                        <i class="bi bi-download me-2"></i> Download
                    </button>
                    
                    {* Link para URL de download direto como alternativa *}
                    <a href="download.php?format=CSV
                        {if $post.categories}{foreach from=$post.categories item=category}&categories[]={$category}{/foreach}{/if}
                        {if $post.crop}&crop={$post.crop}{/if}
                        {if $post.project}&project={$post.project}{/if}
                        {if $post.date_from}&date_from={$post.date_from}{/if}
                        {if $post.date_to}&date_to={$post.date_to}{/if}
                        {if $post.limit}&limit={$post.limit}{/if}"
                        class="btn btn-outline-primary ms-2">
                        <i class="bi bi-link-45deg me-2"></i> URL Direto
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>