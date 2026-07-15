@extends('layouts.public')

@section('title', 'Compare Institutes — EduBase')
@section('meta_description', 'Side-by-side comparison of educational institutions in Bangladesh. Compare fees, curriculum, facilities, location, and more.')

@push('styles')
<link rel="canonical" href="{{ url()->current() }}">
<style>
@media print {
    nav, footer, .flex.items-center, .no-print { display: none !important; }
    .overflow-x-auto { overflow: visible !important; }
    table { box-shadow: none !important; }
    th.sticky { position: static !important; }
}

/* GSMArena Style Adjustments */
.eb-table th, .eb-table td {
    border: 1px solid #e5e7eb;
    padding: 0.6rem 0.85rem;
}
.eb-table thead th {
    background-color: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}
.group-header-cell {
    color: var(--color-primary-700);
    font-weight: 700;
    text-transform: uppercase;
    background-color: #f9fafb;
    vertical-align: top;
    width: 120px;
}
.row-label-cell {
    font-weight: 600;
    color: #4b5563; /* gray-600 */
    background-color: #f9fafb;
    width: 160px;
}

/* TS custom overrides within headers */
.compare-header-select {
    width: 100%;
}
.compare-header-select .ts-control {
    border: 1px solid #d1d5db !important;
    font-weight: 500 !important;
    text-align: left;
    height: auto;
    min-height: 38px;
    padding: 0.35rem 0.5rem;
    /* Allow long institute names to wrap instead of truncating */
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
    word-break: break-word;
    line-height: 1.25;
}
.compare-header-select .ts-control .item {
    white-space: normal;
    word-break: break-word;
    overflow: visible;
}
/* Keep the Tom Select wrapper below the column remove button */
.compare-header-select .ts-wrapper {
    position: relative;
    z-index: 1;
}
.compare-header-select .ts-dropdown {
    text-align: left;
}
</style>
@endpush

@section('content')
<div class="container-page py-8" x-data="compareView()" x-init="initCompare()">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-eyebrow">Side-by-side</span>
            <h1 class="section-title mt-1">Compare Institutes</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-text-secondary">
                <input type="checkbox" checked
                       x-model="hideIdentical"
                       class="rounded border-border text-primary-600 focus:ring-primary-500">
                Hide identical rows
            </label>
            <button @click="window.print()" class="btn btn-secondary btn-sm"><i data-lucide="printer" class="h-4 w-4"></i> Print</button>
            <button @click="copyShareUrl()" class="btn btn-secondary btn-sm"><i data-lucide="share-2" class="h-4 w-4"></i> Share</button>
            <button @click="addColumn()" :disabled="cols.length >= 5" :class="cols.length >= 5 ? 'opacity-50 cursor-not-allowed' : ''" class="btn btn-primary btn-sm"><i data-lucide="plus" class="h-4 w-4"></i> Add Column</button>
        </div>
    </div>

    <template x-if="errorMessage">
        <div class="mb-6 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800" role="alert" x-text="errorMessage"></div>
    </template>

    <div class="card overflow-hidden relative" x-show="cols.length > 0">
        <div class="overflow-x-auto">
            <table class="eb-table w-full text-sm">
                <thead>
                    <tr>
                        <th colspan="2" class="sticky left-0 z-10 bg-surface-muted min-w-[200px]"></th>
                        <template x-for="(col, colIdx) in cols" :key="col.key">
                            <th class="relative min-w-[240px] p-3 pr-9 text-center align-top whitespace-normal bg-surface">
                                <div class="compare-header-select" :id="'select-wrapper-' + col.key" x-init="$nextTick(() => initTomSelect(col.key, colIdx))">
                                    <select :id="'select-' + col.key" class="tom-select-compare">
                                        <option value=""></option>
                                        <template x-if="col.institute">
                                            <option :value="col.institute.uuid" selected x-text="col.institute.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <button @click="removeColumn(colIdx)"
                                        x-show="cols.length > 1"
                                        class="compare-remove absolute right-2 top-2 z-30 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition-colors hover:bg-red-100 hover:text-red-600"
                                        title="Remove column" aria-label="Remove"><i data-lucide="x" style="width:0.85rem;height:0.85rem;stroke-width:3"></i></button>
                            </th>
                        </template>
                    </tr>
                </thead>
                <template x-for="group in groups" :key="group.slug">
                    <tbody x-show="getVisibleCount(group.slug) > 0">
                        <template x-for="(row, rowIdx) in group.rows" :key="row.slug">
                                <tr class="compare-row bg-white hover:bg-gray-50 transition-colors"
                                    x-show="rowIsVisible(group, row)">
                                
                                <template x-if="isFirstVisible(group.slug, rowIdx)">
                                    <th class="group-header-cell sticky left-0 z-10" :rowspan="getVisibleCount(group.slug)" x-text="group.name"></th>
                                </template>

                                <td class="row-label-cell sticky left-[120px] z-10" x-text="row.label"></td>
                                
                                <template x-for="(col, colIdx) in cols" :key="col.key">
                                    <td class="text-center" :class="row.all_identical ? 'text-gray-500' : 'text-gray-900 font-medium'">
                                        <span x-html="getColumnValue(col, group.slug, row.slug)"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </template>
            </table>
        </div>
    </div>
</div>

<script @nonce>
function compareView() {
    return {
        hideIdentical: true,
        cols: [],
        groups: [],
        tsInstances: {},
        prevUuids: '',
        errorMessage: @json($mismatch ? 'You can only compare institutes of the same type. Please choose institutes of the same type.' : ''),

        initCompare() {
            // Initial data parsed from blade if loaded with pre-selected institutes
            const initialInstitutes = @json($matrix->institutes ?? []);
            const initialGroups = @json($matrix->groups ?? []);

            // Normalize all_identical key (DTO serializes as camelCase, API returns snake_case)
            this.groups = initialGroups.map(g => ({
                ...g,
                rows: (g.rows || []).map(r => ({ ...r, all_identical: r.all_identical ?? r.allIdentical ?? false }))
            }));

            // Seed columns
            if (initialInstitutes.length > 0) {
                this.cols = initialInstitutes.map(inst => ({
                    key: 'col-' + Math.random().toString(36).substr(2, 9),
                    uuid: inst.uuid,
                    slug: inst.slug,
                    institute: inst
                }));
            }

            // Ensure we have at least 2 columns
            while (this.cols.length < 2) {
                this.cols.push({
                    key: 'col-' + Math.random().toString(36).substr(2, 9),
                    uuid: null,
                    slug: null,
                    institute: null
                });
            }

            // Track the current set so we can bust the cache when it changes
            this.prevUuids = this.cols.filter(c => c.uuid).map(c => c.uuid).join(',');

            // Mirror the initially-selected institutes into the bottom tray
            this.syncTray();
        },

        initTomSelect(key, index) {
            if (this.tsInstances[key]) {
                return;
            }

            const selectEl = document.getElementById('select-' + key);
            if (!selectEl) return;

            const self = this;
            const ts = new TomSelect(selectEl, {
                valueField: 'uuid',
                labelField: 'name',
                searchField: 'name',
                placeholder: 'Search institute...',
                dropdownParent: 'body',
                preload: false,
                load: function(query, callback) {
                    if (!query.length) return callback();
                    fetch('/api/v1/search/autocomplete?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(json => {
                            callback(json.data || []);
                        }).catch(() => {
                            callback();
                        });
                },
                onChange: function(val) {
                    self.onInstituteChange(index, val);
                }
            });

            this.tsInstances[key] = ts;
            
            // Set initial selected item if exists
            const col = this.cols[index];
            if (col && col.institute) {
                ts.addOption(col.institute);
                ts.setValue(col.institute.uuid, true);
            }
        },

        onInstituteChange(index, uuid) {
            const col = this.cols[index];
            if (!col) return;

            const prev = { uuid: col.uuid, slug: col.slug, institute: col.institute };

            if (!uuid) {
                col.uuid = null;
                col.slug = null;
                col.institute = null;
                this.errorMessage = '';
                this.recalculateMatrix();
                return;
            }
            if (uuid === prev.uuid) return;

            col.uuid = uuid;
            col.slug = null;
            col.institute = null;
            this.errorMessage = '';
            this.recalculateMatrix({ index, prev });
        },

        recalculateMatrix(revert = null) {
            const uuids = this.cols.map(c => c.uuid).filter(Boolean);
            const setKey = uuids.join(',');
            const changed = setKey !== this.prevUuids;

            // Update URL dynamically
            this.updateUrl();

            if (uuids.length < 2) {
                // Not enough items, leave groups as is or empty them
                this.groups = [];
                this.prevUuids = setKey;
                this.syncTray();
                return;
            }

            // When the set changed, force a cache rebuild (old entry is cleared,
            // a fresh one is created for the new institute set).
            const apiUrl = '/api/v1/compare?ids=' + uuids.join(',') + (changed ? '&refresh=1' : '');

            fetch(apiUrl)
                .then(res => {
                    if (!res.ok) return res.json().then(j => Promise.reject(j));
                    return res.json();
                })
                .then(json => {
                    // Update columns models with real hydrated data
                    this.cols.forEach(col => {
                        if (col.uuid) {
                            const found = json.institutes.find(i => i.uuid === col.uuid);
                            if (found) {
                                col.institute = found;
                                col.slug = found.slug;
                            }
                        }
                    });
                    this.groups = json.groups;
                    this.updateUrl();
                    this.prevUuids = setKey;
                    this.errorMessage = '';
                    this.syncTray();
                })
                .catch(err => {
                    if (revert && err && err.mismatch) {
                        // Revert the offending column to its prior institute
                        const col = this.cols[revert.index];
                        if (col) {
                            col.uuid = revert.prev.uuid;
                            col.slug = revert.prev.slug;
                            col.institute = revert.prev.institute;
                            if (this.tsInstances[col.key]) {
                                this.tsInstances[col.key].setValue(revert.prev.uuid || '', true);
                            }
                        }
                        this.errorMessage = err.error || 'Those institutions cannot be compared.';
                        // Rebuild with the now-valid (reverted) set
                        const ruuids = this.cols.map(c => c.uuid).filter(Boolean);
                        const rkey = ruuids.join(',');
                        this.updateUrl();
                        if (ruuids.length >= 2) {
                            fetch('/api/v1/compare?ids=' + ruuids.join(',') + (rkey !== this.prevUuids ? '&refresh=1' : ''))
                                .then(res => res.ok ? res.json() : Promise.reject())
                                .then(json => {
                                    this.cols.forEach(c => {
                                        if (c.uuid) {
                                            const f = json.institutes.find(i => i.uuid === c.uuid);
                                            if (f) { c.institute = f; c.slug = f.slug; }
                                        }
                                    });
                                    this.groups = json.groups;
                                    this.prevUuids = rkey;
                                    this.syncTray();
                                })
                                .catch(() => {});
                        } else {
                            this.groups = [];
                            this.prevUuids = rkey;
                            this.syncTray();
                        }
                    } else {
                        this.errorMessage = (err && err.error) ? err.error : 'Could not load the comparison.';
                    }
                });
        },

        updateUrl() {
            const items = this.cols.filter(c => c.slug).map(c => c.slug);
            if (items.length >= 2) {
                const params = new URLSearchParams();
                items.forEach((slug, idx) => params.set('i' + (idx + 1), slug));
                window.history.replaceState(null, '', '/compare?' + params.toString());
            } else {
                window.history.replaceState(null, '', '/compare');
            }
        },

        // Mirror the current columns into the bottom compare tray so the two
        // stay in sync (the tray is the source of truth elsewhere).
        syncTray() {
            if (typeof window.syncTray !== 'function') return;
            const items = this.cols
                .filter(c => c.institute && c.uuid)
                .map(c => ({
                    uuid: c.uuid,
                    slug: c.slug,
                    name: c.institute.name,
                    typeId: c.institute.institute_type_id ?? c.institute.type_id ?? null,
                }));
            window.syncTray(items);
        },

        addColumn() {
            if (this.cols.length >= 5) return;
            const key = 'col-' + Math.random().toString(36).substr(2, 9);
            this.cols.push({
                key: key,
                uuid: null,
                slug: null,
                institute: null
            });
            this.refreshIcons();
        },

        refreshIcons() {
            if (typeof window.refreshIcons === 'function') {
                this.$nextTick(() => window.refreshIcons());
            }
        },

        removeColumn(index) {
            const col = this.cols[index];
            if (col && this.tsInstances[col.key]) {
                this.tsInstances[col.key].destroy();
                delete this.tsInstances[col.key];
            }
            this.cols.splice(index, 1);
            this.recalculateMatrix();
        },

        getColumnValue(col, groupSlug, rowSlug) {
            if (!col.uuid || !this.groups) return '—';
            const group = this.groups.find(g => g.slug === groupSlug);
            if (!group) return '—';
            const row = group.rows.find(r => r.slug === rowSlug);
            if (!row) return '—';

            // Find index of this column's institute in the matrix returned from API
            // The API returns values ordered match the order of json.institutes
            const instIndex = this.cols.filter(c => c.uuid).indexOf(col);
            if (instIndex === -1) return '—';
            return row.values[instIndex] !== undefined ? row.values[instIndex] : '—';
        },

        rowIsVisible(group, row) {
            if (!this.hideIdentical) return true;
            const visibleDiff = group.rows.filter(r => !r.all_identical).length;
            // If the entire group matches, always show its rows so the table is
            // never blank when at least 2 institutes are compared.
            if (visibleDiff === 0) return true;
            return !row.all_identical;
        },

        getVisibleCount(groupSlug) {
            const group = this.groups.find(g => g.slug === groupSlug);
            if (!group) return 0;
            return group.rows.filter(r => this.rowIsVisible(group, r)).length;
        },

        isFirstVisible(groupSlug, rowIndex) {
            const group = this.groups.find(g => g.slug === groupSlug);
            if (!group) return false;

            const visibleRows = group.rows
                .map((r, idx) => this.rowIsVisible(group, r) ? idx : -1)
                .filter(idx => idx !== -1);
            return visibleRows[0] === rowIndex;
        },

        copyShareUrl() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Comparison URL copied to clipboard!');
            });
        }
    };
}
</script>
@endsection
