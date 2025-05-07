{* templates/partials/data_table.tpl - Tabela de resultados *}
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0"><i class="bi bi-table"></i> Resultados da Consulta</h3>
            <span class="badge bg-primary">{$totalRecords} registros encontrados</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        {if $data}
                            {foreach from=$data[0] key=header item=value}
                                <th>{$header}</th>
                            {/foreach}
                        {/if}
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$data item=row}
                        <tr>
                            {foreach from=$row item=value}
                                <td>
                                    {if $value === null}
                                        <span class="text-muted">NULL</span>
                                    {elseif $value === ''}
                                        <span class="text-muted">-</span>
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
    {if $totalRecords > 10}
        <div class="card-footer text-end">
            <nav aria-label="Paginação de resultados">
                <ul class="pagination justify-content-end mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
    {/if}
</div>