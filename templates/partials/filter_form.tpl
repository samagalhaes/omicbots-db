{* templates/partials/filter_form.tpl - Formulário de filtros *}
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="h5 mb-0"><i class="bi bi-funnel"></i> Filtros de Pesquisa</h3>
    </div>
    <div class="card-body">
        <form method="post" action="">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">Categorias de Dados:</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="lab_measures" id="cat_lab" {if $post.categories && 'lab_measures'|in_array:$post.categories}checked{/if}>
                                <label class="form-check-label" for="cat_lab">Medidas Laboratoriais</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="hormones" id="cat_horm" {if $post.categories && 'hormones'|in_array:$post.categories}checked{/if}>
                                <label class="form-check-label" for="cat_horm">Hormônios</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="ecophysio" id="cat_eco" {if $post.categories && 'ecophysio'|in_array:$post.categories}checked{/if}>
                                <label class="form-check-label" for="cat_eco">Ecofisiologia</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="genes" id="cat_genes" {if $post.categories && 'genes'|in_array:$post.categories}checked{/if}>
                                <label class="form-check-label" for="cat_genes">Genes</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="xrf" id="cat_xrf" {if $post.categories && 'xrf'|in_array:$post.categories}checked{/if}>
                                <label class="form-check-label" for="cat_xrf">XRF</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]" value="spectra" id="cat_spectra" {if $post.categories && 'spectra'|in_array:$post.categories}checked{/if}>
                                <label class="form-check-label" for="cat_spectra">Espectros</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="crop" class="form-label fw-bold">Cultura:</label>
                    <select class="form-select" id="crop" name="crop">
                        <option value="all">Todas as Culturas</option>
                        {foreach from=$crops item=crop}
                            <option value="{$crop}" {if $post.crop == $crop}selected{/if}>{$crop}</option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="project" class="form-label fw-bold">Projeto:</label>
                    <select class="form-select" id="project" name="project">
                        <option value="all">Todos os Projetos</option>
                        {foreach from=$projects item=project}
                            <option value="{$project}" {if $post.project == $project}selected{/if}>{$project}</option>
                        {/foreach}
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="limit" class="form-label fw-bold">Limite de Resultados:</label>
                    <select class="form-select" id="limit" name="limit">
                        <option value="100" {if $post.limit == 100}selected{/if}>100 registros</option>
                        <option value="500" {if $post.limit == 500}selected{/if}>500 registros</option>
                        <option value="1000" {if $post.limit == 1000 || !$post.limit}selected{/if}>1000 registros</option>
                        <option value="5000" {if $post.limit == 5000}selected{/if}>5000 registros</option>
                        <option value="0" {if $post.limit == 0}selected{/if}>Todos os registros</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="date_from" class="form-label fw-bold">Data Inicial:</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{$post.date_from|default:''}">
                </div>
                
                <div class="col-md-6">
                    <label for="date_to" class="form-label fw-bold">Data Final:</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{$post.date_to|default:''}">
                </div>
                
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-search me-2"></i> Buscar Dados
                    </button>
                    <button type="reset" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Limpar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>