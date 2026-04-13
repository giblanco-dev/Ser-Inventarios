/**
 * SER Inventarios — Enfermería: Líneas de detalle dinámicas
 *
 * Reutiliza la lógica de movimientos.js adaptada para el módulo
 * de entregas/devoluciones de enfermería (solo insumos INTE).
 */
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('detail-lines-container');
    const addBtn = document.getElementById('btn-add-line');
    const searchInput = document.getElementById('insumo-search');
    const searchResults = document.getElementById('search-results');
    const form = document.getElementById('enfermeria-form');

    if (!container || !addBtn) return;

    // Determinar si estamos en un formulario de devolución
    const isDevolucion = form && form.dataset.tipo === 'devolucion';

    let lineIndex = container.querySelectorAll('.detail-line').length;
    let searchTimeout;

    // ── Add Detail Line ──────────────────────────────────
    function addDetailLine(insumo = null) {
        const idx = lineIndex++;
        const stockLabel = isDevolucion ? 'Stock Enf.' : 'Stock Global';
        const html = `
        <div class="detail-line" data-index="${idx}">
            <div class="form-group">
                <label>Insumo <span class="required">*</span></label>
                <input type="text" class="form-control insumo-display"
                       value="${insumo ? insumo.Nombre : ''}" readonly
                       placeholder="Buscar insumo...">
                <input type="hidden" name="InsumoID[]" value="${insumo ? insumo.ID : ''}">
                ${insumo ? '<span class="text-xs text-muted">' + (insumo.MedidaDescripcion || '') + ' · ' + stockLabel + ': ' + insumo.Existencia + '</span>' : ''}
            </div>
            <div class="form-group">
                <label>Cantidad <span class="required">*</span></label>
                <input type="number" name="Cantidad[]" class="form-control"
                       min="1" step="1" value="1"
                       ${insumo ? 'max="' + Math.floor(insumo.Existencia) + '"' : ''}
                       required>
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
                const tipo = isDevolucion ? 'devolucion' : 'entrega';
                const stockLabel = isDevolucion ? 'Stock Enf.' : 'Stock Global';

                fetch(`${window.BASE_URL}insumos-enfermeria/search-insumos?q=${encodeURIComponent(term)}&tipo=${tipo}`, {
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
                                <div class="item-meta">${item.MedidaDescripcion || ''} · ${stockLabel}: ${item.Existencia}</div>
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

    // ── Manual Add button ────────────────────────────────
    addBtn.addEventListener('click', () => addDetailLine());
});
