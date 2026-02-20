/**
 * SER Inventarios — Movimientos: Líneas de detalle dinámicas
 */
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('detail-lines-container');
    const addBtn = document.getElementById('btn-add-line');
    const searchInput = document.getElementById('insumo-search');
    const searchResults = document.getElementById('search-results');

    if (!container || !addBtn) return;

    let lineIndex = container.querySelectorAll('.detail-line').length;
    let searchTimeout;

    // ── Add Detail Line ──────────────────────────────────
    function addDetailLine(insumo = null) {
        const idx = lineIndex++;
        const html = `
        <div class="detail-line" data-index="${idx}">
            <div class="form-group">
                <label>Insumo <span class="required">*</span></label>
                <input type="text" class="form-control insumo-display" 
                       value="${insumo ? insumo.Nombre : ''}" readonly 
                       placeholder="Buscar insumo...">
                <input type="hidden" name="InsumoID[]" value="${insumo ? insumo.ID : ''}">
                ${insumo ? '<span class="text-xs text-muted">' + (insumo.MedidaDescripcion || '') + ' · Stock: ' + insumo.Existencia + '</span>' : ''}
            </div>
            <div class="form-group">
                <label>Cantidad <span class="required">*</span></label>
                <input type="number" name="Cantidad[]" class="form-control" min="1" step="1" value="1" required>
            </div>
            <div class="form-group lote-group" style="display: ${insumo && insumo.Caducidad == 1 ? 'block' : 'none'}">
                <label>Nº Lote</label>
                <input type="text" name="NumeroLote[]" class="form-control" placeholder="Lote">
            </div>
            <div class="form-group vencimiento-group" style="display: ${insumo && insumo.Caducidad == 1 ? 'block' : 'none'}">
                <label>Vencimiento</label>
                <input type="date" name="FechaVencimiento[]" class="form-control">
            </div>
            <button type="button" class="btn-remove-line" onclick="this.closest('.detail-line').remove()" title="Quitar línea">✕</button>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    // ── Insumo Search ────────────────────────────────────
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim();
            clearTimeout(searchTimeout);

            if (term.length < 2) {
                searchResults.classList.remove('show');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`${window.BASE_URL}insumos/search?q=${encodeURIComponent(term)}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.length === 0) {
                            searchResults.innerHTML = '<div class="search-result-item text-muted">Sin resultados</div>';
                        } else {
                            searchResults.innerHTML = data.map(item => `
                            <div class="search-result-item" data-insumo='${JSON.stringify(item)}'>
                                <div class="item-name">${item.Nombre}</div>
                                <div class="item-meta">${item.MedidaDescripcion || ''} · Stock: ${item.Existencia}</div>
                            </div>
                        `).join('');
                        }
                        searchResults.classList.add('show');

                        // Click on result
                        searchResults.querySelectorAll('[data-insumo]').forEach(el => {
                            el.addEventListener('click', () => {
                                const insumo = JSON.parse(el.dataset.insumo);
                                addDetailLine(insumo);
                                searchInput.value = '';
                                searchResults.classList.remove('show');
                            });
                        });
                    })
                    .catch(() => {
                        searchResults.classList.remove('show');
                    });
            }, 300);
        });

        // Hide results on click outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('show');
            }
        });
    }

    // ── Barcode Search ───────────────────────────────────
    const barcodeInput = document.getElementById('barcode-search');
    if (barcodeInput) {
        barcodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = barcodeInput.value.trim();
                if (!code) return;

                fetch(`${window.BASE_URL}insumos/search-barcode?code=${encodeURIComponent(code)}`, {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            addDetailLine(data);
                        }
                        barcodeInput.value = '';
                    })
                    .catch(() => alert('Error al buscar código de barra'));
            }
        });
    }

    // ── Manual Add button ────────────────────────────────
    addBtn.addEventListener('click', () => addDetailLine());
});
