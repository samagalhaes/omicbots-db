/**
 * script.js - Enhanced JavaScript for filter functionality
 * Implements the interactive filter behavior similar to FAO STAT
 */

document.addEventListener('DOMContentLoaded', function() {
    // Configurar busca para filtros com radio buttons
    setupRadioFilterSearch('searchSpectraDevice', 'spectra-device-radio');
    
    // Manter o restante da configuração para outros filtros que ainda usam checkboxes
    setupFilterSearch('searchYears', 'year-checkbox');
    setupFilterSearch('searchCrops', 'crop-checkbox');
    
    // Manter a configuração de select/clear all para outros filtros
    setupToggleButtons('selectAllYears', 'clearAllYears', 'year-checkbox');
    setupToggleButtons('selectAllCrops', 'clearAllCrops', 'crop-checkbox');
    
    // Remover botões de Select All/Clear All para radio buttons se existirem
    removeUnneededButtons('selectAllDevices', 'clearAllDevices');
    
    // Demais configurações permanecem as mesmas
    setupDownloadForm();
    setupFilterBoxEffects();
    setupKeyboardNavigation();
});



/**
 * Sets up search filtering for checkboxes
 * @param {string} searchInputId - ID of the search input element
 * @param {string} checkboxClass - Class name of checkboxes to filter
 */
function setupFilterSearch(searchInputId, checkboxClass) {
    const searchInput = document.getElementById(searchInputId);
    if (!searchInput) return;
    
    searchInput.addEventListener('keyup', function() {
        filterCheckboxes(checkboxClass, this.value);
    });
    
    // Clear search when clicking the X button in the search input
    searchInput.addEventListener('search', function() {
        filterCheckboxes(checkboxClass, '');
    });
}

/**
 * Filters checkboxes based on search text
 * @param {string} className - Class name of checkboxes to filter
 * @param {string} searchText - Text to search for
 */
function filterCheckboxes(className, searchText) {
    const checkboxes = document.getElementsByClassName(className);
    const search = searchText.toLowerCase().trim();
    
    // Count visible and matching items
    let visibleCount = 0;
    let totalCount = checkboxes.length;
    
    for (let i = 0; i < checkboxes.length; i++) {
        const checkbox = checkboxes[i];
        const label = checkbox.nextElementSibling;
        const text = label.textContent.toLowerCase();
        const parentOption = checkbox.closest('.filter-option');
        
        // Show/hide based on whether text contains search input
        if (search === '' || text.includes(search)) {
            parentOption.style.display = '';
            visibleCount++;
        } else {
            parentOption.style.display = 'none';
        }
    }
    
    // Show message if no results
    showNoResultsMessage(className, visibleCount === 0 && totalCount > 0 && search !== '');
}

/**
 * Shows or hides a "No results found" message
 * @param {string} className - Class name of the filter
 * @param {boolean} show - Whether to show or hide the message
 */
function showNoResultsMessage(className, show) {
    // Determine the parent filter options container
    let container;
    if (className === 'spectra-device-checkbox') {
        container = document.getElementById('searchSpectraDevice').closest('.filter-box').querySelector('.filter-options');
    } else if (className === 'year-checkbox') {
        container = document.getElementById('searchYears').closest('.filter-box').querySelector('.filter-options');
    } else if (className === 'crop-checkbox') {
        container = document.getElementById('searchCrops').closest('.filter-box').querySelector('.filter-options');
    }
    
    if (!container) return;
    
    // Remove any existing message
    const existingMsg = container.querySelector('.no-results-message');
    if (existingMsg) {
        container.removeChild(existingMsg);
    }
    
    // Add message if needed
    if (show) {
        const msgElement = document.createElement('div');
        msgElement.className = 'no-results-message';
        msgElement.style.padding = '10px';
        msgElement.style.textAlign = 'center';
        msgElement.style.fontStyle = 'italic';
        msgElement.style.color = '#6c757d';
        msgElement.textContent = 'No results found';
        container.appendChild(msgElement);
    }
}

/**
 * Sets up select all and clear all buttons
 * @param {string} selectAllId - ID of the "Select All" button
 * @param {string} clearAllId - ID of the "Clear All" button
 * @param {string} checkboxClass - Class name of checkboxes to toggle
 */
function setupToggleButtons(selectAllId, clearAllId, checkboxClass) {
    const selectAllBtn = document.getElementById(selectAllId);
    const clearAllBtn = document.getElementById(clearAllId);
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            toggleCheckboxes(checkboxClass, true);
        });
    }
    
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            toggleCheckboxes(checkboxClass, false);
        });
    }
}

/**
 * Toggles checkboxes checked state
 * @param {string} className - Class name of checkboxes to toggle
 * @param {boolean} checked - Whether to check or uncheck
 */
function toggleCheckboxes(className, checked) {
    const checkboxes = document.getElementsByClassName(className);
    
    for (let i = 0; i < checkboxes.length; i++) {
        // Only toggle visible checkboxes (respecting search filter)
        if (checkboxes[i].closest('.filter-option').style.display !== 'none') {
            checkboxes[i].checked = checked;
        }
    }
}

/**
 * Sets up the download form to include filter values
 */
function setupDownloadForm() {
    const downloadForm = document.getElementById('downloadForm');
    if (!downloadForm) return;
    
    downloadForm.addEventListener('submit', function(e) {
        const filterForm = document.getElementById('filterForm');
        const hiddenFilterValues = document.getElementById('hiddenFilterValues');
        
        // Limpar inputs ocultos anteriores
        hiddenFilterValues.innerHTML = '';
        
        // Obter todos os checkboxes selecionados
        const checkedBoxes = filterForm.querySelectorAll('input[type=checkbox]:checked');
        checkedBoxes.forEach(function(checkbox) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = checkbox.name;
            input.value = checkbox.value;
            hiddenFilterValues.appendChild(input);
        });
        
        // Obter todos os radio buttons selecionados
        const checkedRadios = filterForm.querySelectorAll('input[type=radio]:checked');
        checkedRadios.forEach(function(radio) {
            // Incluir todos os valores de radio buttons
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = radio.name;
            input.value = radio.value;
            hiddenFilterValues.appendChild(input);
        });
    });
}

/**
 * Sets up visual effects for filter boxes
 */
function setupFilterBoxEffects() {
    // Add focus effect to search inputs
    const searchInputs = document.querySelectorAll('.search-input input');
    
    searchInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.closest('.filter-box').style.boxShadow = '0 0 0 0.25rem rgba(13, 110, 253, 0.25)';
        });
        
        input.addEventListener('blur', function() {
            this.closest('.filter-box').style.boxShadow = '';
        });
    });
    
    // Add selected count indicators to filter sections
    const filterSections = document.querySelectorAll('.filter-section');
    
    filterSections.forEach(section => {
        const title = section.querySelector('.filter-title');
        const checkboxes = section.querySelectorAll('input[type=checkbox]');
        const countSpan = document.createElement('span');
        
        countSpan.className = 'selected-count';
        countSpan.style.marginLeft = '5px';
        countSpan.style.fontSize = '12px';
        countSpan.style.color = '#6c757d';
        title.appendChild(countSpan);
        
        // Update count when checkboxes change
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount(section);
            });
        });
        
        // Initialize count
        updateSelectedCount(section);
    });
}

/**
 * Updates the selected count display for a filter section
 * @param {HTMLElement} section - The filter section element
 */
function updateSelectedCount(section) {
    const countSpan = section.querySelector('.selected-count');
    const checkboxes = section.querySelectorAll('input[type=checkbox]');
    const checkedBoxes = section.querySelectorAll('input[type=checkbox]:checked');
    
    if (checkedBoxes.length > 0) {
        countSpan.textContent = `(${checkedBoxes.length}/${checkboxes.length})`;
    } else {
        countSpan.textContent = '';
    }
}

/**
 * Sets up keyboard navigation in filter options
 */
function setupKeyboardNavigation() {
    const filterOptions = document.querySelectorAll('.filter-options');
    
    filterOptions.forEach(optionsContainer => {
        optionsContainer.addEventListener('keydown', function(e) {
            const options = this.querySelectorAll('.filter-option:not([style*="display: none"])');
            if (options.length === 0) return;
            
            const currentFocus = document.activeElement;
            let currentIndex = -1;
            
            // Find current focused option
            for (let i = 0; i < options.length; i++) {
                if (options[i].contains(currentFocus)) {
                    currentIndex = i;
                    break;
                }
            }
            
            // Handle arrow keys
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextIndex = (currentIndex + 1) % options.length;
                options[nextIndex].querySelector('input').focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : options.length - 1;
                options[prevIndex].querySelector('input').focus();
            }
        });
    });
}

/**
 * Função para configurar a busca em filtros com radio buttons
 * @param {string} searchInputId - ID do campo de busca
 * @param {string} radioClass - Classe dos radio buttons
 */
function setupRadioFilterSearch(searchInputId, radioClass) {
    const searchInput = document.getElementById(searchInputId);
    if (!searchInput) return;
    
    searchInput.addEventListener('keyup', function() {
        filterRadioOptions(radioClass, this.value);
    });
    
    // Limpar a busca quando clicar no X do campo de busca
    searchInput.addEventListener('search', function() {
        filterRadioOptions(radioClass, '');
    });
}

/**
 * Filtra as opções de radio button com base no texto de busca
 * @param {string} className - Classe dos radio buttons
 * @param {string} searchText - Texto a ser buscado
 */
function filterRadioOptions(className, searchText) {
    const radioButtons = document.getElementsByClassName(className);
    const search = searchText.toLowerCase().trim();
    
    // Contar opções visíveis e correspondentes
    let visibleCount = 0;
    let totalCount = radioButtons.length;
    let firstVisibleRadio = null;
    
    for (let i = 0; i < radioButtons.length; i++) {
        const radio = radioButtons[i];
        const label = radio.nextElementSibling;
        const text = label.textContent.toLowerCase();
        const parentOption = radio.closest('.filter-option');
        
        // Mostrar/ocultar com base no texto de busca
        if (search === '' || text.includes(search)) {
            parentOption.style.display = '';
            visibleCount++;
            
            // Armazenar a referência ao primeiro radio button visível
            if (firstVisibleRadio === null) {
                firstVisibleRadio = radio;
            }
        } else {
            parentOption.style.display = 'none';
        }
    }
    
    // Mostrar mensagem se não houver resultados
    showNoResultsMessage(className, visibleCount === 0 && totalCount > 0 && search !== '');
    
    // Verificar se o radio button selecionado está visível
    // Se não estiver, selecionar o primeiro radio button visível
    let hasVisibleSelected = false;
    for (let i = 0; i < radioButtons.length; i++) {
        const radio = radioButtons[i];
        if (radio.checked && radio.closest('.filter-option').style.display !== 'none') {
            hasVisibleSelected = true;
            break;
        }
    }
    
    // Se não houver radio button selecionado visível e houver pelo menos um visível,
    // selecionar o primeiro visível
    if (!hasVisibleSelected && firstVisibleRadio) {
        firstVisibleRadio.checked = true;
    }
}

function removeUnneededButtons(selectAllId, clearAllId) {
    const selectAllBtn = document.getElementById(selectAllId);
    const clearAllBtn = document.getElementById(clearAllId);
    
    if (selectAllBtn) {
        const btnContainer = selectAllBtn.closest('.filter-buttons');
        if (btnContainer) {
            btnContainer.style.display = 'none';
        } else {
            selectAllBtn.style.display = 'none';
        }
    }
    
    if (clearAllBtn) {
        const btnContainer = clearAllBtn.closest('.filter-buttons');
        if (!btnContainer) {
            clearAllBtn.style.display = 'none';
        }
    }
}