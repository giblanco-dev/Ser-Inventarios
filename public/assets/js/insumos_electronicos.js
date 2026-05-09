/**
 * insumos_electronicos.js — Módulo de Activos Electrónicos / TI
 *
 * Funcionalidades:
 * - Lector de código de barras: Enter en #NoSerie mueve foco al siguiente campo
 * - Toggle de contraseñas: botones 👁 en formulario y vista de detalle
 * - Modal de baja desde listado y desde detalle
 * - Mayúsculas automáticas: campos [data-uppercase]
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Mayúsculas automáticas ────────────────────────────────
    document.querySelectorAll('[data-uppercase]').forEach(input => {
        input.addEventListener('input', () => {
            const pos = input.selectionStart;
            input.value = input.value.toUpperCase();
            input.setSelectionRange(pos, pos);
        });
    });

    // ── Lector de código de barras — campo NoSerie ───────────
    // Los lectores de barras emiten keydown con Enter al terminar de escanear.
    // Capturamos ese Enter para no hacer submit prematuro del form;
    // en su lugar movemos el foco al siguiente campo (Modelo).
    const noSerieInput = document.getElementById('NoSerie');
    if (noSerieInput && !noSerieInput.readOnly) {
        noSerieInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Mover al siguiente campo editable
                const modeloInput = document.getElementById('Modelo');
                if (modeloInput) modeloInput.focus();
            }
        });
    }

    // ── Toggle contraseñas en el FORMULARIO ──────────────────
    // Botones con clase .btn-toggle-pass y data-target="<id del input>"
    document.querySelectorAll('.btn-toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const input    = document.getElementById(targetId);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type     = isHidden ? 'text' : 'password';
            btn.textContent = isHidden ? '🙈' : '👁';
            btn.title       = isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña';
        });
    });

    // ── Toggle contraseñas en la vista DETALLE (show.php) ────
    // Muestra el span con el valor real y oculta los asteriscos.
    document.querySelectorAll('.btn-toggle-pass-show').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId   = btn.dataset.target;
            const hiddenSpan = document.getElementById('show-' + targetId);
            const valueSpan  = document.getElementById('val-' + targetId);
            if (!hiddenSpan || !valueSpan) return;

            const isShowing = valueSpan.style.display !== 'none';

            if (isShowing) {
                hiddenSpan.style.display = '';
                valueSpan.style.display  = 'none';
                btn.textContent = '👁';
                btn.title = 'Mostrar contraseña';
            } else {
                hiddenSpan.style.display = 'none';
                valueSpan.style.display  = '';
                btn.textContent = '🙈';
                btn.title = 'Ocultar contraseña';
            }
        });
    });

    // ── Modal de Baja desde DETALLE (show.php) ───────────────
    const btnAbrirBaja   = document.getElementById('btn-abrir-baja');
    const modalBaja      = document.getElementById('modal-baja');
    const btnCerrarBaja  = document.getElementById('btn-cerrar-baja');
    const btnCancelarBaja = document.getElementById('btn-cancelar-baja');

    if (btnAbrirBaja && modalBaja) {
        btnAbrirBaja.addEventListener('click', () => {
            modalBaja.style.display = 'flex';
        });
    }
    if (btnCerrarBaja) {
        btnCerrarBaja.addEventListener('click', () => {
            modalBaja.style.display = 'none';
        });
    }
    if (btnCancelarBaja) {
        btnCancelarBaja.addEventListener('click', () => {
            modalBaja.style.display = 'none';
        });
    }

    // Cerrar modal al hacer clic fuera
    if (modalBaja) {
        modalBaja.addEventListener('click', (e) => {
            if (e.target === modalBaja) {
                modalBaja.style.display = 'none';
            }
        });
    }

    // ── Modal de Baja desde LISTADO (index.php) ──────────────
    // Los botones de baja en el listado tienen data-id y data-noserie.
    document.querySelectorAll('[data-baja-id]').forEach(btn => {
        btn.addEventListener('click', () => {
            const id      = btn.dataset.bajaId;
            const noserie = btn.dataset.noserie || '';
            const modal   = document.getElementById('modal-baja');
            if (!modal) return;

            // Actualizar action del form y texto de confirmación
            const form = modal.querySelector('form');
            if (form) {
                form.action = (window.BASE_URL || '/') + 'insumos-electronicos/baja/' + id;
            }
            const noserieEl = document.getElementById('baja-noserie');
            if (noserieEl) noserieEl.textContent = noserie;

            // Limpiar textarea
            const textarea = document.getElementById('input-comentario-baja');
            if (textarea) textarea.value = '';

            modal.style.display = 'flex';
        });
    });

    // Cerrar modal de baja del listado
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            if (modal) modal.style.display = 'none';
        });
    });

});
