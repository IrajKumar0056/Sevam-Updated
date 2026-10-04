/**
 * SEVAM - Vanilla JavaScript Interaction Script
 * Pure Vanilla JS, zero libraries
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Navigation Toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navMenu = document.querySelector('.nav-menu');
    const navActions = document.querySelector('.nav-actions');

    if (mobileBtn && navMenu) {
        mobileBtn.addEventListener('click', function () {
            navMenu.classList.toggle('show');
            if (navActions) {
                navActions.classList.toggle('show');
            }
        });
    }

    // 2. Registration Role Switcher
    const roleTabs = document.querySelectorAll('.role-select-btn');
    const formProvider = document.getElementById('form-food-provider');
    const formGroup = document.getElementById('form-social-group');

    if (roleTabs.length > 0 && formProvider && formGroup) {
        roleTabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetRole = this.getAttribute('data-role');

                roleTabs.forEach(t => {
                    t.classList.remove('btn-primary');
                    t.classList.add('btn-secondary');
                });
                this.classList.remove('btn-secondary');
                this.classList.add('btn-primary');

                if (targetRole === 'provider') {
                    formProvider.style.display = 'block';
                    formGroup.style.display = 'none';
                } else {
                    formProvider.style.display = 'none';
                    formGroup.style.display = 'block';
                }
            });
        });
    }

    // 3. FSSAI Status Toggle in Provider Registration & Profile
    const fssaiStatusSelect = document.getElementById('fssai_status');
    const fssaiNumberWrap = document.getElementById('fssai_number_wrap');
    const fssaiNumberInput = document.getElementById('fssai_number');

    if (fssaiStatusSelect && fssaiNumberWrap) {
        function toggleFssai() {
            if (fssaiStatusSelect.value === 'Certified') {
                fssaiNumberWrap.style.display = 'block';
                if (fssaiNumberInput) {
                    fssaiNumberInput.setAttribute('required', 'required');
                }
            } else {
                fssaiNumberWrap.style.display = 'none';
                if (fssaiNumberInput) {
                    fssaiNumberInput.removeAttribute('required');
                }
            }
        }
        fssaiStatusSelect.addEventListener('change', toggleFssai);
        // Initial state
        toggleFssai();
    }

    // 4. Modal Interactions (Request Food Modal)
    const modal = document.getElementById('food-request-modal');
    const modalCloseBtns = document.querySelectorAll('.modal-close, .modal-cancel');
    const requestButtons = document.querySelectorAll('.btn-open-request-modal');

    if (modal) {
        requestButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const foodId = this.getAttribute('data-id');
                const foodName = this.getAttribute('data-name');
                const providerName = this.getAttribute('data-provider');
                const availableQty = this.getAttribute('data-qty');
                const unit = this.getAttribute('data-unit');
                const price = this.getAttribute('data-price');
                const availableDate = this.getAttribute('data-date');
                const startTime = this.getAttribute('data-start');
                const endTime = this.getAttribute('data-end');

                // Populate modal fields
                document.getElementById('modal-food-id').value = foodId;
                document.getElementById('modal-food-title').textContent = foodName;
                document.getElementById('modal-provider-name').textContent = providerName;
                document.getElementById('modal-available-qty').textContent = availableQty + ' ' + unit;
                document.getElementById('modal-price').textContent = '₹' + price;
                document.getElementById('modal-slot').textContent = availableDate + ' (' + startTime + ' - ' + endTime + ')';

                const qtyInput = document.getElementById('modal-req-qty');
                if (qtyInput) {
                    qtyInput.max = availableQty;
                    qtyInput.value = availableQty;
                    document.getElementById('modal-qty-unit-label').textContent = unit;
                }

                const dateInput = document.getElementById('modal-req-date');
                if (dateInput) {
                    dateInput.value = availableDate;
                }

                modal.classList.add('active');
            });
        });

        modalCloseBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                modal.classList.remove('active');
            });
        });

        // Close on overlay click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    // 5. Client-side Form Validation for Password Matching
    const registerForms = document.querySelectorAll('form[data-validate="register"]');
    registerForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const pass = form.querySelector('input[name="password"]');
            const confirmPass = form.querySelector('input[name="confirm_password"]');
            if (pass && confirmPass && pass.value !== confirmPass.value) {
                e.preventDefault();
                showToast('Passwords do not match! Please check and retry.', 'error');
                confirmPass.focus();
            }
        });
    });

    // 6. Simple Toast Notification System
    window.showToast = function (message, type = 'info') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.style.cssText = 'min-width:260px;max-width:380px;padding:12px 18px;border-radius:8px;font-size:14px;font-weight:500;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);color:#fff;opacity:0;transform:translateY(10px);transition:all 0.2s ease;pointer-events:auto;';

        if (type === 'error') {
            toast.style.backgroundColor = '#b91c1c';
        } else if (type === 'success') {
            toast.style.backgroundColor = '#15803d';
        } else {
            toast.style.backgroundColor = '#1e293b';
        }

        toast.textContent = message;
        toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 250);
        }, 4000);
    };

    // 7. Cross-Origin Iframe Session Preservation (Fallback if 3rd-party cookies blocked)
    (function () {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const sidFromUrl = urlParams.get('sid') || urlParams.get('PHPSESSID');
            if (sidFromUrl) {
                sessionStorage.setItem('sevam_sid', sidFromUrl);
            }
            const activeSid = sidFromUrl || sessionStorage.getItem('sevam_sid');
            if (activeSid) {
                // Keep sid parameter on internal dashboard links and forms
                document.querySelectorAll('a[href^="/"]').forEach(function (link) {
                    const href = link.getAttribute('href');
                    if (href && !href.startsWith('//') && !href.includes('sid=') && !href.includes('logout.php')) {
                        const sep = href.includes('?') ? '&' : '?';
                        link.setAttribute('href', href + sep + 'sid=' + encodeURIComponent(activeSid));
                    }
                });
                document.querySelectorAll('form').forEach(function (form) {
                    const action = form.getAttribute('action') || '';
                    if (!action.startsWith('http') && !form.querySelector('input[name="sid"]')) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'sid';
                        hidden.value = activeSid;
                        form.appendChild(hidden);
                    }
                });
            }
        } catch (e) {}
    })();
});
