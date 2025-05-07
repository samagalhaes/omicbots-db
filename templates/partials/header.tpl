{* templates/partials/header.tpl - Template de cabeçalho *}
<div class="header">
    <div class="row align-items-center my-4">
        <div class="col-md-8">
            <h1>{$smarty.const.APP_NAME}</h1>
            <p class="lead">Consulte e baixe dados de pesquisas agrícolas, similar ao FAOSTAT</p>
        </div>
        <div class="col-md-4 text-end">
            <img src="assets/images/logo-agricultural.png" alt="Logo" class="img-fluid" style="max-height: 80px;">
        </div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Página Inicial</li>
        </ol>
    </nav>
    <hr>
</div>