<?php
/* Smarty version 4.5.5, created on 2025-05-08 15:45:12
  from '/var/www/html/omicbots-db/templates/index.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_681cd1883b6dc2_67933290',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'baa2fffb19ded8cfff57b78558ccc8999587e2a8' => 
    array (
      0 => '/var/www/html/omicbots-db/templates/index.tpl',
      1 => 1746718939,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_681cd1883b6dc2_67933290 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/var/www/html/omicbots-db/vendor/smarty/smarty/libs/plugins/modifier.replace.php','function'=>'smarty_modifier_replace',),));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (defined('APP_NAME') ? constant('APP_NAME') : null);?>
</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
                        <div class="col-md-3 sidebar">
                <h4 class="sidebar-header">FILTERS</h4>

                <form method="post" action="index.php" id="filterForm">
                                        <div class="filter-section">
                        <div class="filter-title">SPECTRA DEVICE</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter results, e.g. NIRS"
                                    id="searchSpectraDevice">
                            </div>
                            <div class="filter-options">
                                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['spectraDevices']->value, 'device', false, NULL, 'deviceLoop', array (
  'first' => true,
  'index' => true,
));
$_smarty_tpl->tpl_vars['device']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['device']->value) {
$_smarty_tpl->tpl_vars['device']->do_else = false;
$_smarty_tpl->tpl_vars['__smarty_foreach_deviceLoop']->value['index']++;
$_smarty_tpl->tpl_vars['__smarty_foreach_deviceLoop']->value['first'] = !$_smarty_tpl->tpl_vars['__smarty_foreach_deviceLoop']->value['index'];
?>
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input spectra-device-radio" type="radio"
                                                value="<?php echo $_smarty_tpl->tpl_vars['device']->value;?>
" id="device_<?php echo smarty_modifier_replace($_smarty_tpl->tpl_vars['device']->value,' ','_');?>
"
                                                name="spectra_device" <?php if (((isset($_smarty_tpl->tpl_vars['filters']->value['spectra_device'])) && $_smarty_tpl->tpl_vars['filters']->value['spectra_device'] == $_smarty_tpl->tpl_vars['device']->value) || (!(isset($_smarty_tpl->tpl_vars['filters']->value['spectra_device'])) && (isset($_smarty_tpl->tpl_vars['__smarty_foreach_deviceLoop']->value['first']) ? $_smarty_tpl->tpl_vars['__smarty_foreach_deviceLoop']->value['first'] : null))) {?>checked<?php }?>>
                                        <label class="form-check-label"
                                            for="device_<?php echo smarty_modifier_replace($_smarty_tpl->tpl_vars['device']->value,' ','_');?>
"><?php echo $_smarty_tpl->tpl_vars['device']->value;?>
</label>
                                    </div>
                                </div>
                                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                            </div>
                        </div>
                    </div>

                                        <div class="filter-section">
                        <div class="filter-title">YEARS</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter results, e.g. 2023"
                                    id="searchYears">
                            </div>
                            <div class="filter-options">
                                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['years']->value, 'year');
$_smarty_tpl->tpl_vars['year']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['year']->value) {
$_smarty_tpl->tpl_vars['year']->do_else = false;
?>
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input year-checkbox" type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['year']->value;?>
"
                                                id="year_<?php echo $_smarty_tpl->tpl_vars['year']->value;?>
" name="years[]"
                                                <?php if ((isset($_smarty_tpl->tpl_vars['filters']->value['years'])) && in_array($_smarty_tpl->tpl_vars['year']->value,$_smarty_tpl->tpl_vars['filters']->value['years'])) {?>checked<?php }?>>
                                            <label class="form-check-label" for="year_<?php echo $_smarty_tpl->tpl_vars['year']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['year']->value;?>
</label>
                                        </div>
                                    </div>
                                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                            </div>
                            <div class="filter-buttons">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="selectAllYears">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllYears">Clear
                                    All</button>
                            </div>
                        </div>
                    </div>

                                        <div class="filter-section">
                        <div class="filter-title">CROP TYPE</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter results, e.g. Maize"
                                    id="searchCrops">
                            </div>
                            <div class="filter-options">
                                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['cropTypes']->value, 'crop');
$_smarty_tpl->tpl_vars['crop']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['crop']->value) {
$_smarty_tpl->tpl_vars['crop']->do_else = false;
?>
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input crop-checkbox" type="checkbox" value="<?php echo $_smarty_tpl->tpl_vars['crop']->value;?>
"
                                                id="crop_<?php echo smarty_modifier_replace($_smarty_tpl->tpl_vars['crop']->value,' ','_');?>
" name="crop_types[]"
                                                <?php if ((isset($_smarty_tpl->tpl_vars['filters']->value['crop_types'])) && in_array($_smarty_tpl->tpl_vars['crop']->value,$_smarty_tpl->tpl_vars['filters']->value['crop_types'])) {?>checked<?php }?>>
                                            <label class="form-check-label"
                                                for="crop_<?php echo smarty_modifier_replace($_smarty_tpl->tpl_vars['crop']->value,' ','_');?>
"><?php echo $_smarty_tpl->tpl_vars['crop']->value;?>
</label>
                                        </div>
                                    </div>
                                <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                            </div>
                            <div class="filter-buttons">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="selectAllCrops">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllCrops">Clear
                                    All</button>
                            </div>
                        </div>
                    </div>

                                        <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-primary" type="submit">Apply Filters</button>
                    </div>
                </form>
            </div>

                        <div class="col-md-9 main-content">
                <h2><?php echo (defined('APP_NAME') ? constant('APP_NAME') : null);?>
</h2>
                <p>Access and download crop spectral data filtered by various parameters. Select your preferred filters
                    on the left panel.</p>

                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="data-tab" data-bs-toggle="tab" data-bs-target="#data"
                            type="button" role="tab" aria-controls="data" aria-selected="true">Data</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="metadata-tab" data-bs-toggle="tab" data-bs-target="#metadata"
                            type="button" role="tab" aria-controls="metadata" aria-selected="false">Metadata</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="help-tab" data-bs-toggle="tab" data-bs-target="#help" type="button"
                            role="tab" aria-controls="help" aria-selected="false">Help</button>
                    </li>
                </ul>

                                <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="data" role="tabpanel" aria-labelledby="data-tab">
                        <div class="download-options mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Download Options</h5>
                                            <form method="post" action="index.php" id="downloadForm">
                                                <div class="mb-3">
                                                    <label for="downloadFormat" class="form-label">Format</label>
                                                    <select class="form-select" id="downloadFormat" name="format">
                                                        <option value="csv" selected>CSV</option>
                                                        <option value="excel">Excel</option>
                                                        <option value="json">JSON</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" name="download" value="true">
                                                                                                <div id="hiddenFilterValues"></div>
                                                <div class="d-grid gap-2">
                                                    <button class="btn btn-download" type="submit">Download</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Data Preview</h5>
                                            <p>Selected entries: <span class="badge bg-primary"><?php echo $_smarty_tpl->tpl_vars['recordCount']->value;?>
</span>
                                            </p>
                                            <p>Selected columns: <span class="badge bg-primary"><?php echo $_smarty_tpl->tpl_vars['columnCount']->value;?>
</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                                                <div class="data-table">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Year</th>
                                        <th>Spectra Device</th>
                                        <th>Crop</th>
                                        <th>Cultivar</th>
                                        <th>Morphology</th>
                                        <th>Local</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['data']->value, 'row');
$_smarty_tpl->tpl_vars['row']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['row']->value) {
$_smarty_tpl->tpl_vars['row']->do_else = false;
?>
                                        <tr>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['ID'];?>
</td>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['Year'];?>
</td>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['Spectra_device'];?>
</td>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['Crop'];?>
</td>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['Intensity'];?>
</td>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['Morphology'];?>
</td>
                                            <td><?php echo $_smarty_tpl->tpl_vars['row']->value['Local'];?>
</td>
                                        </tr>
                                    <?php
}
if ($_smarty_tpl->tpl_vars['row']->do_else) {
?>
                                        <tr>
                                            <td colspan="9" class="text-center">No data available</td>
                                        </tr>
                                    <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>
                                </tbody>
                            </table>

                                                        <nav aria-label="Data pagination">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>

                                        <div class="tab-pane fade" id="metadata" role="tabpanel" aria-labelledby="metadata-tab">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Dataset Information</h5>
                                <p>This dataset contains spectral measurements from various agricultural samples. The
                                    data includes:</p>
                                <ul>
                                    <li><strong>Spectra Device:</strong> The instrument used for spectral acquisition
                                        (NIRS, FTIR, Raman, XRF)</li>
                                    <li><strong>Crop Type:</strong> The agricultural crop being analyzed</li>
                                    <li><strong>Cultivar:</strong> The specific variety of the crop</li>
                                    <li><strong>Wavelength:</strong> The spectral wavelength in nm or cm<sup>-1</sup>
                                        depending on the technique</li>
                                    <li><strong>Intensity:</strong> The measured spectral intensity</li>
                                    <li><strong>Morphology:</strong> The physical characteristics of the sample</li>
                                    <li><strong>Local:</strong> The geographical location where the sample was collected
                                    </li>
                                </ul>
                                <h6>Data Collection Methods</h6>
                                <p>All spectral measurements were performed according to standard protocols. Sample
                                    preparation followed ISO guidelines for agricultural products.</p>
                                <h6>Citation</h6>
                                <p>When using this data, please cite: Agricultural Research Institute (2023). Spectral
                                    Database of Agricultural Crops.</p>
                            </div>
                        </div>
                    </div>

                                        <div class="tab-pane fade" id="help" role="tabpanel" aria-labelledby="help-tab">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">How to Use This Tool</h5>
                                <ol>
                                    <li><strong>Apply Filters:</strong> Use the filter panel on the left to select
                                        specific criteria for your data.</li>
                                    <li><strong>Preview Data:</strong> After applying filters, the data table will show
                                        a preview of the selected data.</li>
                                    <li><strong>Download:</strong> Choose your preferred format and click the Download
                                        button to get your data.</li>
                                </ol>
                                <h6>Filter Options</h6>
                                <ul>
                                    <li><strong>Spectra Device:</strong> Filter by the type of spectroscopic instrument
                                        used.</li>
                                    <li><strong>Years:</strong> Select specific years of data collection.</li>
                                    <li><strong>Crop Type:</strong> Filter by the agricultural crop.</li>
                                </ul>
                                <h6>Contact Support</h6>
                                <p>If you need assistance or have questions about the data, please contact <a
                                        href="mailto:support@agrispectra.org">support@agrispectra.org</a>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo '<script'; ?>
 src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="js/script.js"><?php echo '</script'; ?>
>
</body>

</html><?php }
}
