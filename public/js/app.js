/* Kisan Enterprises ERP — shared UI behaviour (no build step required) */
(function () {
    'use strict';

    // ---- Sidebar Toggle & Collapse (Desktop + Mobile) -------------------
    function isDesktop() {
        return window.innerWidth >= 992;
    }

    function toggleSidebar() {
        if (isDesktop()) {
            var isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);
            try {
                localStorage.setItem('ke_sidebar_collapsed', isCollapsed ? '1' : '0');
            } catch (err) {}
            updateSidebarToggleState(isCollapsed);
        } else {
            document.body.classList.toggle('sidebar-open');
        }
    }

    function updateSidebarToggleState(isCollapsed) {
        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
        });
    }

    // Sync body class on load if html has sidebar-collapsed from early inline script
    if (document.documentElement.classList.contains('sidebar-collapsed')) {
        document.body.classList.add('sidebar-collapsed');
        updateSidebarToggleState(true);
    }

    document.addEventListener('click', function (e) {
        var toggleBtn = e.target.closest('[data-sidebar-toggle]');
        if (toggleBtn) {
            e.preventDefault();
            toggleSidebar();
        } else if (e.target.closest('.sidebar-brand') && document.documentElement.classList.contains('sidebar-collapsed') && isDesktop()) {
            e.preventDefault();
            toggleSidebar();
        } else if (e.target.closest('.sidebar-backdrop')) {
            document.body.classList.remove('sidebar-open');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.body.classList.remove('sidebar-open');
        } else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
            if (tag !== 'input' && tag !== 'textarea' && !e.target.isContentEditable) {
                e.preventDefault();
                toggleSidebar();
            }
        }
    });

    // ---- Confirmation dialogs ------------------------------------------
    // <form data-confirm="Message" data-confirm-title="Title" data-confirm-button="Finalize" data-confirm-variant="danger">
    var modalEl = document.getElementById('confirmModal');
    var pendingForm = null;
    if (modalEl && window.bootstrap) {
        var modal = new bootstrap.Modal(modalEl);
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.matches('form[data-confirm]') || form.dataset.confirmed === '1') return;
            e.preventDefault();
            pendingForm = form;
            modalEl.querySelector('.modal-title').textContent = form.dataset.confirmTitle || 'Please confirm';
            modalEl.querySelector('.modal-body p').textContent = form.dataset.confirm;
            var btn = modalEl.querySelector('[data-confirm-ok]');
            btn.textContent = form.dataset.confirmButton || 'Confirm';
            btn.className = 'btn btn-' + (form.dataset.confirmVariant || 'primary');
            modal.show();
        }, true);
        modalEl.querySelector('[data-confirm-ok]').addEventListener('click', function () {
            if (!pendingForm) return;
            pendingForm.dataset.confirmed = '1';
            modal.hide();
            if (pendingForm.requestSubmit) pendingForm.requestSubmit(); else pendingForm.submit();
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (pendingForm && pendingForm.dataset.confirmed !== '1') pendingForm = null;
        });
    }

    // ---- Prevent double submission (client side; server also enforces) --
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (e.defaultPrevented || form.method.toLowerCase() === 'get') return;
        if (form.dataset.submitting === '1') { e.preventDefault(); return; }
        form.dataset.submitting = '1';
        form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (b) {
            b.classList.add('is-loading');
            b.setAttribute('aria-busy', 'true');
        });
        // Safety: re-enable after 15s in case of a network error (server token still blocks replays)
        setTimeout(function () {
            form.dataset.submitting = '';
            form.querySelectorAll('.is-loading').forEach(function (b) { b.classList.remove('is-loading'); b.removeAttribute('aria-busy'); });
        }, 15000);
    });

    // Restore buttons when the page is shown from bfcache (back button)
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-submitting="1"]').forEach(function (f) { f.dataset.submitting = ''; });
        document.querySelectorAll('.is-loading').forEach(function (b) { b.classList.remove('is-loading'); });
    });

    // ---- Auto-submit filters on select change ----------------------------
    document.querySelectorAll('form[data-autosubmit] select').forEach(function (sel) {
        sel.addEventListener('change', function () { sel.form.submit(); });
    });

    // ---- Sync real DOM value to uppercase for .text-uppercase inputs -----
    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!el || el.tagName !== 'INPUT' || !el.classList.contains('text-uppercase')) return;
        var upper = el.value.toUpperCase();
        if (el.value !== upper) {
            var start = el.selectionStart;
            var end = el.selectionEnd;
            el.value = upper;
            try {
                if (typeof start === 'number' && typeof end === 'number') {
                    el.setSelectionRange(start, end);
                }
            } catch (err) {}
        }
    });

    // ---- Mobile-Only Collapsible Filter Bars (< 768px) -------------------
    (function initMobileCollapsibleFilters() {
        var isHindi = (document.documentElement.lang || '').toLowerCase().indexOf('hi') === 0;
        var labelSearchFilter = isHindi ? 'फ़िल्टर और खोज (Search & Filter)' : 'Search & Filter';
        var labelActive = isHindi ? 'सक्रिय' : 'Active';
        var labelReset = isHindi ? 'रीसेट' : 'Reset';

        // Also ensure any custom filter containers without explicit class are wrapped
        document.querySelectorAll('.app-content form[data-autosubmit], .app-content form.filter-bar').forEach(function (form) {
            if (form.closest('#inventoryFilterDrawer') || form.closest('#paymentFilterDrawer')) return;
            if (!form.closest('.erp-filter-collapsible')) {
                var card = form.closest('.card');
                if (card && card.querySelectorAll('table, .mobile-card-list, .list-group').length === 0) {
                    card.classList.add('erp-filter-collapsible');
                } else if (form.parentElement && form.parentElement.children.length === 1 && !form.parentElement.classList.contains('card')) {
                    form.parentElement.classList.add('erp-filter-collapsible');
                } else {
                    form.classList.add('erp-filter-collapsible');
                }
            }
        });

        document.querySelectorAll('.erp-filter-collapsible').forEach(function (panel, idx) {
            if (panel.dataset.mobileFilterInit === '1') return;
            panel.dataset.mobileFilterInit = '1';

            var form = panel.tagName === 'FORM' ? panel : panel.querySelector('form');
            var activeCount = 0;
            var resetHref = null;

            if (form) {
                form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select').forEach(function (el) {
                    var val = (el.value || '').trim();
                    var name = el.getAttribute('name') || '';
                    if (!val) return;
                    if (name === 'sort' && val === 'name_asc') return;
                    if (name === 'preset' && val === 'this_month') return;
                    activeCount++;
                });

                var resetLink = form.querySelector('a.btn[href]');
                if (resetLink) {
                    resetHref = resetLink.getAttribute('href');
                }
            }

            var isInsideCard = panel.parentElement && panel.parentElement.classList.contains('card') && !panel.classList.contains('card');
            var bar = document.createElement('div');
            bar.className = 'erp-mobile-filter-bar d-md-none ' + (isInsideCard ? 'is-in-card' : 'is-standalone');

            var panelId = panel.id || ('erpMobileFilterPanel_' + idx);
            panel.id = panelId;

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'erp-mobile-filter-toggle';
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-controls', panelId);

            var badgeHtml = activeCount > 0
                ? '<span class="badge bg-success text-white rounded-pill ms-1" style="font-size: 0.7rem;">' + activeCount + ' ' + labelActive + '</span>'
                : '';

            btn.innerHTML =
                '<span class="d-inline-flex align-items-center gap-2">' +
                    '<i class="bi bi-sliders2 text-success"></i>' +
                    '<span>' + labelSearchFilter + '</span>' +
                    badgeHtml +
                '</span>' +
                '<i class="bi bi-chevron-down erp-filter-chevron"></i>';

            btn.addEventListener('click', function () {
                var isOpen = panel.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            bar.appendChild(btn);

            if (activeCount > 0 && resetHref) {
                var resetBtn = document.createElement('a');
                resetBtn.href = resetHref;
                resetBtn.className = 'btn btn-sm btn-outline-danger erp-mobile-filter-reset d-inline-flex align-items-center gap-1';
                resetBtn.innerHTML = '<i class="bi bi-x-circle"></i><span>' + labelReset + '</span>';
                bar.appendChild(resetBtn);
            }

            panel.parentNode.insertBefore(bar, panel);
        });
    })();

    // ---- Image preview for file inputs -----------------------------------
    document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var target = document.querySelector(input.dataset.preview);
            if (!target || !input.files[0]) return;
            target.src = URL.createObjectURL(input.files[0]);
            target.classList.remove('d-none');
        });
    });

    // ---- PWA Service Worker Registration --------------------------------
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').then(function () {
                // Registered successfully
            }).catch(function (err) {
                console.warn('PWA service worker registration notice:', err);
            });
        });
    }

    // ---- PWA Install Prompt Banner ---------------------------------------
    var deferredInstallPrompt = null;
    var installBanner = document.getElementById('pwaInstallBanner');
    var installBtn = document.getElementById('pwaInstallBtn');
    var dismissBtn = document.getElementById('pwaDismissBtn');

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredInstallPrompt = e;

        // Check if recently dismissed in last 7 days
        var dismissedTime = localStorage.getItem('kisan_pwa_dismissed');
        if (dismissedTime && (Date.now() - parseInt(dismissedTime, 10) < 7 * 24 * 60 * 60 * 1000)) {
            return;
        }

        if (installBanner) {
            installBanner.classList.remove('d-none');
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', function () {
            if (deferredInstallPrompt) {
                deferredInstallPrompt.prompt();
                deferredInstallPrompt.userChoice.then(function (choice) {
                    if (choice.outcome === 'accepted') {
                        localStorage.setItem('kisan_pwa_installed', '1');
                    }
                    deferredInstallPrompt = null;
                    if (installBanner) installBanner.classList.add('d-none');
                });
            }
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            localStorage.setItem('kisan_pwa_dismissed', Date.now().toString());
            if (installBanner) installBanner.classList.add('d-none');
        });
    }

    // ---- Offline & Online Network Monitoring ----------------------------
    var toast = document.getElementById('networkStatusToast');
    function updateNetworkStatus() {
        if (!navigator.onLine) {
            if (toast) {
                toast.classList.remove('d-none');
                toast.classList.remove('alert-success');
                toast.classList.add('alert-danger');
                var msg = toast.querySelector('#networkStatusMsg');
                if (msg) msg.textContent = window.kisanI18nOffline || 'You are currently offline. Transactions require active connection.';
            }
        } else {
            if (toast && !toast.classList.contains('d-none')) {
                toast.classList.remove('alert-danger');
                toast.classList.add('alert-success');
                var msg = toast.querySelector('#networkStatusMsg');
                if (msg) msg.textContent = window.kisanI18nOnline || 'Back online!';
                setTimeout(function () {
                    toast.classList.add('d-none');
                }, 3000);
            }
        }
    }
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
    if (!navigator.onLine) updateNetworkStatus();
})();
