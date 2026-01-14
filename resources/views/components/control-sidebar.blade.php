<aside class="control-sidebar control-sidebar-dark">
    <div class="p-3 control-sidebar-content">
        <h5 class="mb-2">Customize AdminLTE</h5>
        <hr class="mb-3">

        <div class="mb-3">
            <p class="text-sm text-muted mb-2">Dark Mode</p>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-dark-mode" data-setting="dark-mode">
                <label for="ctl-dark-mode" class="custom-control-label">Dark Mode</label>
            </div>
        </div>

        <div class="mb-3">
            <p class="text-sm text-muted mb-2">Header Options</p>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-navbar-fixed" data-setting="layout-navbar-fixed">
                <label for="ctl-navbar-fixed" class="custom-control-label">Fixed</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-dropdown-legacy" data-setting="dropdown-legacy">
                <label for="ctl-dropdown-legacy" class="custom-control-label">Dropdown Legacy Offset</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-navbar-no-border" data-setting="navbar-no-border">
                <label for="ctl-navbar-no-border" class="custom-control-label">No border</label>
            </div>
        </div>

        <div class="mb-3">
            <p class="text-sm text-muted mb-2">Sidebar Options</p>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-sidebar-collapsed" data-setting="sidebar-collapse">
                <label for="ctl-sidebar-collapsed" class="custom-control-label">Collapsed</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-sidebar-fixed" data-setting="layout-fixed">
                <label for="ctl-sidebar-fixed" class="custom-control-label">Fixed</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-sidebar-mini" data-setting="sidebar-mini">
                <label for="ctl-sidebar-mini" class="custom-control-label">Sidebar Mini</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-sidebar-mini-md" data-setting="sidebar-mini-md">
                <label for="ctl-sidebar-mini-md" class="custom-control-label">Sidebar Mini MD</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-sidebar-mini-xs" data-setting="sidebar-mini-xs">
                <label for="ctl-sidebar-mini-xs" class="custom-control-label">Sidebar Mini XS</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-nav-flat" data-setting="nav-flat">
                <label for="ctl-nav-flat" class="custom-control-label">Nav Flat Style</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-nav-legacy" data-setting="nav-legacy">
                <label for="ctl-nav-legacy" class="custom-control-label">Nav Legacy Style</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-nav-compact" data-setting="nav-compact">
                <label for="ctl-nav-compact" class="custom-control-label">Nav Compact</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-nav-child-indent" data-setting="nav-child-indent">
                <label for="ctl-nav-child-indent" class="custom-control-label">Nav Child Indent</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-nav-child-hide" data-setting="nav-child-hide-on-collapse">
                <label for="ctl-nav-child-hide" class="custom-control-label">Nav Child Hide on Collapse</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-sidebar-no-expand" data-setting="sidebar-no-expand">
                <label for="ctl-sidebar-no-expand" class="custom-control-label">Disable Hover/Focus Auto-Expand</label>
            </div>
        </div>

        <div class="mb-3">
            <p class="text-sm text-muted mb-2">Footer Options</p>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="ctl-footer-fixed" data-setting="layout-footer-fixed">
                <label for="ctl-footer-fixed" class="custom-control-label">Fixed</label>
            </div>
        </div>
    </div>
</aside>

@push('scripts')
<script>
    (function () {
        const body = document.body;
        const mainHeader = document.querySelector('.main-header');
        const navSidebars = Array.from(document.querySelectorAll('.nav-sidebar'));
        const controlSidebar = document.querySelector('.control-sidebar');
        const inputs = controlSidebar ? Array.from(controlSidebar.querySelectorAll('[data-setting]')) : [];
        const storageKey = 'adminlte.customizer';

        if (!inputs.length) {
            return;
        }

        const optionMap = {
            'dark-mode': { target: 'body', className: 'dark-mode' },
            'layout-navbar-fixed': { target: 'body', className: 'layout-navbar-fixed' },
            'dropdown-legacy': { target: 'header', className: 'dropdown-legacy' },
            'navbar-no-border': { target: 'header', className: 'navbar-no-border' },
            'sidebar-collapse': { target: 'body', className: 'sidebar-collapse' },
            'layout-fixed': { target: 'body', className: 'layout-fixed' },
            'sidebar-mini': { target: 'body', className: 'sidebar-mini' },
            'sidebar-mini-md': { target: 'body', className: 'sidebar-mini-md' },
            'sidebar-mini-xs': { target: 'body', className: 'sidebar-mini-xs' },
            'nav-flat': { target: 'nav', className: 'nav-flat' },
            'nav-legacy': { target: 'nav', className: 'nav-legacy' },
            'nav-compact': { target: 'nav', className: 'nav-compact' },
            'nav-child-indent': { target: 'nav', className: 'nav-child-indent' },
            'nav-child-hide-on-collapse': { target: 'nav', className: 'nav-child-hide-on-collapse' },
            'sidebar-no-expand': { target: 'body', className: 'sidebar-no-expand' },
            'layout-footer-fixed': { target: 'body', className: 'layout-footer-fixed' },
        };

        const stored = loadStoredState();
        const state = buildInitialState(stored);

        applyState(state);
        syncInputs(state);

        inputs.forEach((input) => {
            input.addEventListener('change', () => {
                const setting = input.getAttribute('data-setting');
                const isChecked = input.checked;
                state[setting] = isChecked;
                applySetting(setting, isChecked);
                storeState(state);
            });
        });

        function buildInitialState(saved) {
            const initial = {};
            inputs.forEach((input) => {
                const key = input.getAttribute('data-setting');
                const option = optionMap[key];
                if (!option) {
                    return;
                }
                initial[key] = getClassState(option);
            });
            return Object.assign(initial, saved);
        }

        function applyState(currentState) {
            Object.keys(optionMap).forEach((key) => {
                applySetting(key, !!currentState[key]);
            });
        }

        function applySetting(setting, enabled) {
            const option = optionMap[setting];
            if (!option) {
                return;
            }

            if (option.target === 'body') {
                body.classList.toggle(option.className, enabled);
            } else if (option.target === 'header' && mainHeader) {
                mainHeader.classList.toggle(option.className, enabled);
            } else if (option.target === 'nav') {
                navSidebars.forEach((nav) => {
                    nav.classList.toggle(option.className, enabled);
                });
            }

            if (setting === 'dark-mode' && mainHeader) {
                if (enabled) {
                    mainHeader.classList.add('navbar-dark', 'navbar-gray-dark');
                    mainHeader.classList.remove('navbar-light', 'navbar-white');
                } else {
                    mainHeader.classList.add('navbar-light', 'navbar-white');
                    mainHeader.classList.remove('navbar-dark', 'navbar-gray-dark');
                }
            }
        }

        function syncInputs(currentState) {
            inputs.forEach((input) => {
                const key = input.getAttribute('data-setting');
                input.checked = !!currentState[key];
            });
        }

        function getClassState(option) {
            if (option.target === 'body') {
                return body.classList.contains(option.className);
            }
            if (option.target === 'header' && mainHeader) {
                return mainHeader.classList.contains(option.className);
            }
            if (option.target === 'nav' && navSidebars.length) {
                return navSidebars[0].classList.contains(option.className);
            }
            return false;
        }

        function loadStoredState() {
            try {
                const raw = localStorage.getItem(storageKey);
                return raw ? JSON.parse(raw) : {};
            } catch (error) {
                return {};
            }
        }

        function storeState(currentState) {
            try {
                localStorage.setItem(storageKey, JSON.stringify(currentState));
            } catch (error) {
                // Ignore storage errors.
            }
        }
    })();
</script>
@endpush
