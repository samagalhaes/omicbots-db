{* templates/index.tpl - Main template file *}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$smarty.const.APP_NAME}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            {* Sidebar with Filters *}
            <div class="col-md-3 sidebar">
                <h4 class="sidebar-header">FILTERS</h4>

                <form method="post" action="index.php" id="filterForm">
                    {* Spectra Device Filter *}
                    <div class="filter-section">
                        <div class="filter-title">SPECTRA DEVICE</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter results, e.g. NIRS"
                                    id="searchSpectraDevice">
                            </div>
                            <div class="filter-options">
                                {foreach from=$spectraDevices item=device name=deviceLoop}
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input spectra-device-radio" type="radio"
                                                value="{$device}" id="device_{$device|replace:' ':'_'}"
                                                name="spectra_device" {if (isset($filters.spectra_device) && $filters.spectra_device == $device) || 
                            (!isset($filters.spectra_device) && $smarty.foreach.deviceLoop.first)}checked{/if}>
                                        <label class="form-check-label"
                                            for="device_{$device|replace:' ':'_'}">{$device}</label>
                                    </div>
                                </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>

                    {* Years Filter *}
                    <div class="filter-section">
                        <div class="filter-title">YEARS</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter results, e.g. 2023"
                                    id="searchYears">
                            </div>
                            <div class="filter-options">
                                {foreach from=$years item=year}
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input year-checkbox" type="checkbox" value="{$year}"
                                                id="year_{$year}" name="years[]"
                                                {if isset($filters.years) && in_array($year, $filters.years)}checked{/if}>
                                            <label class="form-check-label {if $year == 0}na-label{/if}" for="year_{$year}">
                                                {if $year == 0}
                                                    N/A
                                                {else}
                                                    {$year}
                                                {/if}
                                            </label>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                            <div class="filter-buttons">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="selectAllYears">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllYears">Clear
                                    All</button>
                            </div>
                        </div>
                    </div>

                    {* Crop Type Filter *}
                    <div class="filter-section">
                        <div class="filter-title">CROP TYPE</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter results, e.g. Maize"
                                    id="searchCrops">
                            </div>
                            <div class="filter-options">
                                {foreach from=$cropTypes item=crop}
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input crop-checkbox" type="checkbox" value="{$crop}"
                                                id="crop_{$crop|replace:' ':'_'}" name="crop_types[]"
                                                {if isset($filters.crop_types) && in_array($crop, $filters.crop_types)}checked{/if}>
                                            <label class="form-check-label"
                                                for="crop_{$crop|replace:' ':'_'}">{$crop}</label>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                            <div class="filter-buttons">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    id="selectAllCrops">Select All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllCrops">Clear
                                    All</button>
                            </div>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">PROJECT</div>
                        <div class="filter-box">
                            <div class="search-input">
                                <input type="text" class="form-control" placeholder="Filter projects, e.g. OmiBots"
                                    id="searchProjects">
                            </div>
                            <div class="filter-options">
                                {foreach from=$projects item=project}
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input project-checkbox" type="checkbox"
                                                value="{$project}"
                                                id="project_{$project|replace:' ':'_'|replace:'-':'_'|replace:'.':'_'}"
                                                name="projects[]"
                                                {if isset($filters.projects) && in_array($project, $filters.projects)}checked{/if}>
                                            <label class="form-check-label {if $project == 'N/A'}na-label{/if}"
                                                for="project_{$project|replace:' ':'_'|replace:'-':'_'|replace:'.':'_'}">
                                                {$project}
                                            </label>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                            <div class="filter-buttons">
                                <button type="button" class="btn btn-sm" id="selectAllProjects">Select All</button>
                                <button type="button" class="btn btn-sm" id="clearAllProjects">Clear All</button>
                            </div>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-title">ADDITIONAL DATA</div>
                        <div class="filter-box">
                            <div class="filter-options">
                                {foreach from=$dataCategories key=categoryKey item=category}
                                    <div class="filter-option">
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox"
                                                value="{$categoryKey}" id="category_{$categoryKey}" name="data_categories[]"
                                                {if isset($filters.data_categories) && in_array($categoryKey, $filters.data_categories)}checked{/if}>
                                            <label class="form-check-label" for="category_{$categoryKey}">
                                                {$category.name}
                                                {if isset($category.count)}
                                                    <span class="category-count">({$category.count})</span>
                                                {/if}
                                            </label>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                            <div class="filter-buttons">
                                <button type="button" class="btn btn-sm" id="selectAllCategories">Select All</button>
                                <button type="button" class="btn btn-sm" id="clearAllCategories">Clear All</button>
                            </div>
                        </div>
                    </div>

                    {* Apply Filters Button *}
                    <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-primary" type="submit">Apply Filters</button>
                    </div>
                </form>
            </div>

            {* Main Content *}
            <div class="col-md-9 main-content">
                <h2>{$smarty.const.APP_NAME}</h2>
                <p>Access and download crop spectral data filtered by various parameters. Select your preferred filters
                    on the left panel.</p>

                {* Navigation Tabs *}
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

                {* Tab Content *}
                <div class="tab-content" id="myTabContent">
                    {* Data Tab *}
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
                                                {* Copy filter values from the filter form *}
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
                                            <p>Selected entries: <span class="badge bg-primary">{$recordCount}</span>
                                            </p>
                                            <p>Selected columns: <span class="badge bg-primary">{$columnCount}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {* Data Table *}
                        <div class="data-table">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Project</th>
                                        <th>Code Field</th>
                                        <th>Test site</th>
                                        <th>Spectra Device</th>
                                        <th>Crop</th>
                                        <th>Cultivar</th>
                                        <th>Morphology</th>
                                        <th>Local</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$data item=row}
                                        <tr>
                                            <td>{$row.ID}</td>
                                            <td>{$row.Date}</td>
                                            <td>{$row.Project}</td>
                                            <td>{$row.Code_field}</td>
                                            <td>{$row.Test_site}</td>
                                            <td>{$row.Spectra_device}</td>
                                            <td>{$row.Crop}</td>
                                            <td>{$row.Cultivar}</td>
                                            <td>{$row.Morphology}</td>
                                            <td>{$row.Local}</td>
                                        </tr>
                                    {foreachelse}
                                        <tr>
                                            <td colspan="9" class="text-center">No data available</td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>

                            {* Pagination - This would be implemented with actual paging logic *}
                            {* <nav aria-label="Data pagination">
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
                            </nav> *}
                        </div>
                    </div>

                    {* Metadata Tab *}
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

                    {* Help Tab *}
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>