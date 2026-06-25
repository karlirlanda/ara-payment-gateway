document.addEventListener('alpine:init', () => {
    // Selectable admin table: the header checkbox toggles every row checkbox, and the
    // first `stick` columns freeze on horizontal scroll. Column 1 (the checkbox) is
    // frozen in pure CSS; this measures the remaining identifier columns (whose widths
    // vary) and pins them with cumulative left offsets, re-running on resize and after
    // Livewire re-renders so the offsets stay correct.
    Alpine.data('adminTable', (opts = {}) => ({
        stick: opts.stick || 1,
        sortIndex: null,
        sortDir: null,

        init() {
            this.applyFreeze = this.applyFreeze.bind(this);
            this.reapply = this.reapply.bind(this);

            this.$nextTick(() => {
                this.setupSort();
                this.applyFreeze();
            });

            this._resizeObserver = new ResizeObserver(this.applyFreeze);
            this._resizeObserver.observe(this.$root);

            const table = this.$root.querySelector('table');
            if (table) {
                // Livewire re-renders (filter / paginate / reset) morph the table and strip the
                // JS-applied freeze classes + offsets. An in-place morph (e.g. Reset reusing the
                // same wire:keys) updates cell *text* and strips the class via an *attribute*
                // change — neither of which a childList observer catches — so watch every
                // mutation type. We disconnect while re-applying so our own writes don't loop.
                this._observeOptions = { childList: true, subtree: true, attributes: true, characterData: true };
                this._mutationObserver = new MutationObserver(() => {
                    this._mutationObserver.disconnect();
                    this.$nextTick(() => {
                        this.reapply();
                        this._mutationObserver.observe(table, this._observeOptions);
                    });
                });
                this._mutationObserver.observe(table, this._observeOptions);
            }
        },

        reapply() {
            this.applyFreeze();
            this.applySort();
        },

        toggleAll(event) {
            this.$root.querySelectorAll('tbody input[type=checkbox]').forEach((checkbox) => {
                checkbox.checked = event.target.checked;
            });
        },

        applyFreeze() {
            if (this.stick <= 1) {
                return;
            }

            const table = this.$root.querySelector('table');
            if (! table) {
                return;
            }

            const headCells = [...table.querySelectorAll('thead > tr > th')];
            let left = 0;

            // Column 1 (checkbox) is already sticky via CSS; start the offset past it
            // and pin columns 2..stick on top of it.
            for (let i = 0; i < this.stick && i < headCells.length; i++) {
                if (i >= 1) {
                    const columnCells = [headCells[i], ...table.querySelectorAll(`tbody > tr > *:nth-child(${i + 1})`)];
                    columnCells.forEach((cell) => {
                        cell.style.left = `${left}px`;
                        cell.classList.add('is-stuck');
                    });
                }

                left += headCells[i].getBoundingClientRect().width;
            }
        },

        // Client-side column sort: clicking a (non-checkbox) header reorders the current
        // page's rows by that column, numeric-aware, toggling asc/desc. Visual only —
        // fits the demo template; the active sort is re-applied after Livewire updates.
        setupSort() {
            const table = this.$root.querySelector('table');
            if (! table) {
                return;
            }

            [...table.querySelectorAll('thead > tr > th')].forEach((th, index) => {
                if (th.classList.contains('admin-col-select') || th.dataset.sortReady) {
                    return;
                }
                th.dataset.sortReady = '1';
                th.classList.add('cursor-pointer', 'select-none');
                th.addEventListener('click', () => {
                    this.sortDir = this.sortIndex === index && this.sortDir === 'asc' ? 'desc' : 'asc';
                    this.sortIndex = index;
                    table.querySelectorAll('thead > tr > th[data-sort-dir]').forEach((h) => h.removeAttribute('data-sort-dir'));
                    th.setAttribute('data-sort-dir', this.sortDir);
                    this.applySort();
                });
            });
        },

        applySort() {
            if (this.sortIndex === null) {
                return;
            }

            const table = this.$root.querySelector('table');
            const tbody = table?.querySelector('tbody');
            if (! tbody) {
                return;
            }

            const rows = [...tbody.querySelectorAll(':scope > tr')].filter((r) => r.querySelectorAll('td').length > 1);
            const cellText = (tr) => (tr.children[this.sortIndex]?.textContent || '').trim();
            const toNumber = (s) => {
                const n = parseFloat(s.replace(/[,\s₩%]/g, ''));

                return Number.isNaN(n) ? null : n;
            };

            rows.sort((a, b) => {
                const av = cellText(a);
                const bv = cellText(b);
                const an = toNumber(av);
                const bn = toNumber(bv);
                const cmp = an !== null && bn !== null ? an - bn : av.localeCompare(bv, undefined, { numeric: true });

                return this.sortDir === 'asc' ? cmp : -cmp;
            });

            rows.forEach((row) => tbody.appendChild(row));
        },
    }));

    // List tools: client-side column show/hide + row-density toggle for a list page.
    // Applied to the page root; reads the table within it. Choices persist in
    // localStorage, and re-apply after Livewire re-renders so they survive paging/filtering.
    Alpine.data('listTools', (key = 'list') => ({
        hiddenCols: Alpine.$persist([]).as(`${key}-hidden-cols`),
        density: Alpine.$persist('comfortable').as(`${key}-density`),
        cols: [],
        // Mobile only: the filter card collapses behind a "Filters" toggle; filterCount
        // drives its badge. Desktop ignores both (the card is always shown there).
        filtersOpen: false,
        filterCount: 0,

        init() {
            this.apply = this.apply.bind(this);
            this.countFilters = this.countFilters.bind(this);
            this.$nextTick(() => {
                this.readCols();
                this.apply();
                this.countFilters();
                const bar = this.$root.querySelector('[data-filter-bar]');
                if (bar) {
                    bar.addEventListener('input', this.countFilters);
                    bar.addEventListener('change', this.countFilters);
                }
            });
            document.addEventListener('livewire:navigated', () => this.$nextTick(() => {
                this.readCols();
                this.apply();
                this.countFilters();
            }));

            const table = this.$root.querySelector('table');
            if (table) {
                this._observer = new MutationObserver(() => this.$nextTick(() => {
                    this.apply();
                    this.countFilters();
                }));
                this._observer.observe(table, { childList: true, subtree: true });
            }
        },

        // Count active filters by scanning the filter bar's controls for non-default
        // values — generic, so it works without knowing each page's property names.
        countFilters() {
            const bar = this.$root.querySelector('[data-filter-bar]');
            let n = 0;
            if (bar) {
                bar.querySelectorAll('input, select').forEach((el) => {
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        return;
                    }
                    if (el.value && String(el.value).trim() !== '') {
                        n++;
                    }
                });
            }
            this.filterCount = n;
        },

        // Toggleable columns = every header except the checkbox (index 0) and the actions column.
        readCols() {
            const table = this.$root.querySelector('table');
            if (! table) {
                return;
            }
            this.cols = [...table.querySelectorAll('thead th')]
                .map((th, i) => ({ i, label: th.textContent.trim() }))
                .filter((c) => c.i > 0 && c.label && ! /^(manage|관리)$/i.test(c.label));
        },

        isHidden(i) {
            return this.hiddenCols.includes(i);
        },

        toggle(i) {
            this.hiddenCols = this.isHidden(i) ? this.hiddenCols.filter((x) => x !== i) : [...this.hiddenCols, i];
            this.apply();
        },

        toggleDensity() {
            this.density = this.density === 'compact' ? 'comfortable' : 'compact';
            this.apply();
        },

        apply() {
            const table = this.$root.querySelector('table');
            if (! table) {
                return;
            }
            const total = table.querySelectorAll('thead th').length;
            for (let i = 0; i < total; i++) {
                const hide = this.hiddenCols.includes(i);
                table.querySelectorAll(`thead th:nth-child(${i + 1}), tbody td:nth-child(${i + 1})`).forEach((cell) => {
                    cell.style.display = hide ? 'none' : '';
                });
            }
            table.classList.toggle('admin-table--compact', this.density === 'compact');
        },
    }));

    // Breadcrumb trail for the current page, populated by the tabs store on each navigation.
    Alpine.store('nav', { crumbs: [] });

    // The history-tabs ("tags-view") state lives in a global store rather than a
    // per-element Alpine component. This survives Livewire SPA navigation cleanly:
    // the strip markup is a pure view bound to this store, so it can re-render/morph
    // without owning any state or event listeners. The previous @persist + per-element
    // x-for approach orphaned its clones (ReferenceError: tab is not defined) and
    // duplicated pills when navigating across a non-app layout (e.g. confirm-password).
    Alpine.store('tabs', {
        items: Alpine.$persist([
            { title: 'Dashboard', path: '/dashboard', route: 'dashboard', affix: true },
        ]).as('admin-tabs'),
        current: window.location.pathname,

        capture() {
            // Self-heal: drop any duplicate paths left over in localStorage.
            const seen = new Set();
            this.items = this.items.filter((t) => {
                if (seen.has(t.path)) {
                    return false;
                }
                seen.add(t.path);

                return true;
            });

            // Always keep the Dashboard tab pinned, even for older persisted data.
            this.items = this.items.map((t) => (t.path === '/dashboard' ? { ...t, affix: true } : t));
            if (! this.items.some((t) => t.path === '/dashboard')) {
                this.items.unshift({ title: 'Dashboard', path: '/dashboard', route: 'dashboard', affix: true });
            }

            this.current = window.location.pathname;

            const el = document.getElementById('page-meta');
            if (! el) {
                // Non-admin page (e.g. settings): keep the tabs, just don't add one,
                // and clear the breadcrumb trail.
                Alpine.store('nav').crumbs = [];

                return;
            }

            const path = '/' + (el.dataset.path || '').replace(/^\/+/, '');
            const tab = {
                title: el.dataset.title || 'Untitled',
                path,
                route: el.dataset.route || '',
                affix: path === '/dashboard',
            };
            const existing = this.items.findIndex((t) => t.path === tab.path);
            if (existing === -1) {
                this.items.push(tab);
            } else {
                this.items[existing] = { ...this.items[existing], title: tab.title };
            }

            let crumbs = [];
            try {
                crumbs = JSON.parse(el.dataset.breadcrumb || '[]');
            } catch (e) {
                crumbs = [];
            }
            Alpine.store('nav').crumbs = crumbs;
        },

        isActive(tab) {
            return this.current.replace(/\/+$/, '') === tab.path.replace(/\/+$/, '');
        },

        close(tab) {
            if (! tab || tab.affix) {
                return;
            }
            const wasActive = this.isActive(tab);
            const idx = this.items.findIndex((t) => t.path === tab.path);
            this.items = this.items.filter((t) => t.path !== tab.path);
            if (wasActive) {
                const next = this.items[idx] || this.items[idx - 1] || this.items[0];
                if (next) {
                    window.Livewire.navigate(next.path);
                }
            }
        },

        closeOthers(tab) {
            this.items = this.items.filter((t) => t.affix || t.path === tab.path);
        },

        closeAll() {
            this.items = this.items.filter((t) => t.affix);
            const dash = this.items[0];
            if (dash) {
                window.Livewire.navigate(dash.path);
            }
        },
    });

    // Capture the current page once on load and after every SPA navigation.
    // Registered exactly once per document, so listeners never accumulate.
    Alpine.store('tabs').capture();
    document.addEventListener('livewire:navigated', () => Alpine.store('tabs').capture());
});
