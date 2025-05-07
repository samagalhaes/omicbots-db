{* templates/partials/footer.tpl - Template de rodapé *}
<div class="footer mt-5">
    <hr>
    <div class="row">
        <div class="col-md-6">
            <p>{$smarty.const.APP_NAME} &copy; {$currentYear} - Versão {$smarty.const.APP_VERSION}</p>
        </div>
        <div class="col-md-6 text-end">
            <p class="text-muted">Inspirado no <a href="https://www.fao.org/faostat/en/#data/QCL" target="_blank">FAOSTAT</a></p>
        </div>
    </div>
</div>