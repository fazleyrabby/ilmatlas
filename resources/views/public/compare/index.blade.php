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
    height: 38px;
    min-height: 38px;
    padding: 0.35rem 0.5rem;
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

    <div class="card overflow-hidden relative" x-show="cols.length > 0">
        <button x-show="cols.length > 2" @click="removeLast()"
                class="absolute bottom-4 left-4 z-20 flex h-11 w-11 items-center justify-center rounded-full bg-gray-600 text-white shadow-lg transition hover:bg-gray-700"
                title="Remove last column (min 2)">
            <i data-lucide="minus" class="h-5 w-5"></i>
        </button>
        <div class="overflow-x-auto">
            <table class="eb-table w-full text-sm">
                <thead>
                    <tr>
                        <th colspan="2" class="sticky left-0 z-10 bg-surface-muted min-w-[200px]"></th>
                        <template x-for="(col, colIdx) in cols" :key="col.key">
                            <th class="relative min-w-[240px] p-3 pr-9 text-center bg-surface">
                                <div class="compare-header-select" :id="'select-wrapper-' + col.key" x-init="$nextTick(() => initTomSelect(col.key, colIdx))">
                                    <select :id="'select-' + col.key" class="tom-select-compare">
                                        <option value=""></option>
                                        <template x-if="col.institute">
                                            <option :value="col.institute.uuid" selected x-text="col.institute.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <button @click="removeColumn(colIdx)"
                                        class="compare-remove absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition-colors hover:bg-red-100 hover:text-red-600"
                                        title="Remove column" aria-label="Remove"><i data-lucide="x" style="width:0.85rem;height:0.85rem;stroke-width:3"></i></button>
                            </th>
                        </template>
                    </tr>
                </thead>
                <template x-for="group in groups" :key="group.slug">
                    <tbody x-show="getVisibleCount(group.slug) > 0">
                        <template x-for="(row, rowIdx) in group.rows" :key="row.slug">
                            <tr class="compare-row bg-white hover:bg-gray-50 transition-colors"
                                x-show="!(hideIdentical && row.all_identical)">
                                
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

            col.uuid = uuid || null;
            col.slug = null;
            col.institute = null;
            this.recalculateMatrix();
        },

        recalculateMatrix() {
            const uuids = this.cols.map(c => c.uuid).filter(Boolean);
            
            // Update URL dynamically
            this.updateUrl();

            if (uuids.length < 2) {
                // Not enough items, leave groups as is or empty them
                this.groups = [];
                return;
            }

            fetch('/api/v1/compare?ids=' + uuids.join(','))
                .then(res => {
                    if (!res.ok) throw new Error();
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
                })
                .catch(() => {
                    // Mismatched types etc.
                });
        },

        updateUrl() {
            const slugs = this.cols.map(c => c.slug).filter(Boolean);
            if (slugs.length >= 2) {
                const newUrl = '/compare/' + slugs.join('-vs-');
                window.history.replaceState(null, '', newUrl);
            } else {
                window.history.replaceState(null, '', '/compare');
            }
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

        removeLast() {
            if (this.cols.length <= 2) return;
            const col = this.cols[this.cols.length - 1];
            if (col && this.tsInstances[col.key]) {
                this.tsInstances[col.key].destroy();
                delete this.tsInstances[col.key];
            }
            this.cols.pop();
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

        getVisibleCount(groupSlug) {
            const group = this.groups.find(g => g.slug === groupSlug);
            if (!group) return 0;
            if (!this.hideIdentical) return group.rows.length;
            return group.rows.filter(r => !r.all_identical).length;
        },

        isFirstVisible(groupSlug, rowIndex) {
            const group = this.groups.find(g => g.slug === groupSlug);
            if (!group) return false;
            
            let visibleRows = [];
            group.rows.forEach((r, idx) => {
                if (!(this.hideIdentical && r.all_identical)) {
                    visibleRows.push(idx);
                }
            });
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
