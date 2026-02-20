/**
 * SER Inventarios — JavaScript Principal
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar Toggle (Mobile) ─────────────────────────
    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay?.classList.toggle('open');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }

    // ── Flash Message Auto-dismiss (4s) ─────────────────
    document.querySelectorAll('.flash-message').forEach(msg => {
        // Close button
        const closeBtn = msg.querySelector('.flash-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-10px)';
                setTimeout(() => msg.remove(), 200);
            });
        }

        // Auto dismiss
        setTimeout(() => {
            if (msg.parentNode) {
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-10px)';
                setTimeout(() => msg.remove(), 200);
            }
        }, 4000);
    });

    // ── Confirm Modal ───────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            const message = el.dataset.confirm || '¿Estás seguro?';
            const form = el.closest('form') || document.getElementById(el.dataset.formId);
            const modal = document.getElementById('confirm-modal');

            if (modal) {
                const modalMessage = modal.querySelector('.modal-message');
                const modalConfirm = modal.querySelector('.modal-confirm-btn');
                if (modalMessage) modalMessage.textContent = message;
                modal.classList.add('show');

                // Set up confirm action
                const newBtn = modalConfirm.cloneNode(true);
                modalConfirm.parentNode.replaceChild(newBtn, modalConfirm);
                newBtn.addEventListener('click', () => {
                    if (form) form.submit();
                    modal.classList.remove('show');
                });

                // Cancel
                modal.querySelectorAll('.modal-cancel').forEach(btn => {
                    btn.addEventListener('click', () => modal.classList.remove('show'));
                });
            } else {
                // Fallback to native confirm
                if (confirm(message) && form) {
                    form.submit();
                }
            }
        });
    });

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('show');
            }
        });
    });

    // ── Live Search with Debounce (300ms) ───────────────
    let searchTimeout;
    document.querySelectorAll('[data-live-search]').forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                input.closest('form')?.submit();
            }, 300);
        });
    });

    // ── Uppercase Input ─────────────────────────────────
    document.querySelectorAll('[data-uppercase]').forEach(input => {
        input.addEventListener('input', () => {
            input.value = input.value.toUpperCase();
        });
    });

    // ── Toggle Caducidad Fields ──────────────────────────
    const caducidadCheck = document.getElementById('Caducidad');
    const diasAviso = document.getElementById('dias-aviso-group');
    if (caducidadCheck && diasAviso) {
        const toggleDias = () => {
            diasAviso.style.display = caducidadCheck.checked ? 'block' : 'none';
        };
        toggleDias();
        caducidadCheck.addEventListener('change', toggleDias);
    }
});
