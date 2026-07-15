import { createIcons, icons } from 'lucide';

const STORAGE_KEY = 'edubase_compare';
const MAX_ITEMS = 5;

function getStored() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    } catch {
        return [];
    }
}

function setStored(items) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
}

function navigateCompare(items) {
    const valid = items.filter(i => i.slug);
    if (valid.length >= 2) {
        const params = new URLSearchParams();
        valid.forEach((item, idx) => params.set('i' + (idx + 1), item.slug));
        // refresh=1 forces the server to clear + rebuild the comparison cache
        window.location.href = '/compare?' + params.toString() + '&refresh=1';
    } else {
        window.location.href = '/institutes';
    }
}

function renderTray() {
    let tray = document.getElementById('compareTray');
    if (!tray) return;

    const items = getStored();
    updateCompareIndicator();

    if (items.length === 0) {
        tray.classList.add('hidden');
        return;
    }

    tray.classList.remove('hidden');

    const list = tray.querySelector('.compare-list');
    const btn = tray.querySelector('.compare-cta');

    list.innerHTML = items.map(item => `
        <span class="chip chip-active">
            ${item.name}
            <button data-remove="${item.uuid}" class="compare-remove" aria-label="Remove"><i data-lucide="x" style="width:0.85rem;height:0.85rem;stroke-width:3"></i></button>
        </span>
    `).join('');

    createIcons({ icons });

    btn.disabled = items.length < 2;

    if (items.length >= 2) {
        const params = new URLSearchParams();
        items.forEach((item, idx) => params.set('i' + (idx + 1), item.slug));
        btn.onclick = () => { window.location.href = '/compare?' + params.toString(); };
    }
}

function updateCompareIndicator() {
    const items = getStored();
    document.querySelectorAll('[data-compare-count]').forEach(el => {
        if (items.length > 0) {
            el.textContent = items.length;
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
}

function showToast(message, type = 'error') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 left-1/2 z-[100] flex -translate-x-1/2 flex-col gap-2';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const palette = {
        error: 'bg-red-600 text-white',
        success: 'bg-emerald-600 text-white',
        info: 'bg-gray-800 text-white',
    };
    toast.className = `pointer-events-auto rounded-md px-4 py-2.5 text-sm font-medium shadow-lg ${palette[type] || palette.info}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 300ms ease';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

window.compareAdd = function (uuid, slug, name, typeId) {
    let items = getStored();
    if (items.length >= MAX_ITEMS) {
        showToast('Maximum ' + MAX_ITEMS + ' institutes can be compared.');
        return;
    }
    if (items.find(i => i.uuid === uuid)) {
        showToast('Institute already in comparison.');
        return;
    }
    // Smart guard: only allow comparing institutes of the same type
    const existingType = items.find(i => i.typeId)?.typeId;
    if (existingType && typeId && String(existingType) !== String(typeId)) {
        showToast('You can only compare institutes of the same type. Please choose a similar type.');
        return;
    }
    items.push({ uuid, slug, name, typeId: typeId || null });
    setStored(items);

    const isOnComparePage = window.location.pathname === '/compare';
    if (isOnComparePage && items.length >= 2) {
        // Mirror the addition onto the compare page (it reloads + rebuilds cache)
        navigateCompare(items);
    } else {
        renderTray();
        updateButtonStates();
    }
};

// Set the tray to exactly the given items and re-render, WITHOUT navigating.
// Used by the compare page so the bottom bar mirrors its current columns.
window.syncTray = function (itemList) {
    setStored((itemList || []).filter(i => i && i.uuid));
    renderTray();
    updateButtonStates();
};

window.compareRemove = function (uuid) {
    let items = getStored().filter(i => i.uuid !== uuid);
    setStored(items);

    const isOnComparePage = window.location.pathname === '/compare';
    if (isOnComparePage) {
        navigateCompare(items);
    } else {
        renderTray();
        updateButtonStates();
    }
};

window.compareClear = function () {
    setStored([]);
    const isOnComparePage = window.location.pathname === '/compare';
    if (isOnComparePage) {
        window.location.href = '/institutes';
    } else {
        renderTray();
        updateButtonStates();
    }
};

window.compareRemoveBySlug = function (slug) {
    let items = getStored().filter(i => i.slug !== slug);
    setStored(items);
    navigateCompare(items);
};

function updateButtonStates() {
    const items = getStored();
    document.querySelectorAll('.compare-btn').forEach(btn => {
        const uuid = btn.dataset.uuid;
        const inCompare = items.some(i => i.uuid === uuid);
        if (inCompare) {
            btn.textContent = 'Remove from Compare';
            btn.classList.remove('text-indigo-600', 'hover:text-indigo-800', 'bg-indigo-50', 'hover:bg-indigo-100');
            btn.classList.add('text-red-600', 'hover:text-red-800', 'bg-red-50', 'hover:bg-red-100');
        } else {
            btn.textContent = '+ Add to Compare';
            btn.classList.remove('text-red-600', 'hover:text-red-800', 'bg-red-50', 'hover:bg-red-100');
            btn.classList.add('text-indigo-600', 'hover:text-indigo-800', 'bg-indigo-50', 'hover:bg-indigo-100');
        }
    });
}

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.compare-btn');
    if (!btn) return;
    e.preventDefault();

    const uuid = btn.dataset.uuid;
    const slug = btn.dataset.slug;
    const name = btn.dataset.name;
    const typeId = btn.dataset.typeId;

    if (!uuid) return;

    const items = getStored();
    const existing = items.findIndex(i => i.uuid === uuid);

    if (existing !== -1) {
        compareRemove(uuid);
    } else {
        compareAdd(uuid, slug, name, typeId);
    }
});

document.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('[data-remove]');
    if (!removeBtn) return;
    e.preventDefault();
    compareRemove(removeBtn.dataset.remove);
});

document.addEventListener('click', (e) => {
    const slugBtn = e.target.closest('[data-remove-slug]');
    if (!slugBtn) return;
    e.preventDefault();
    compareRemoveBySlug(slugBtn.dataset.removeSlug);
});

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-clear-compare]')) {
        e.preventDefault();
        compareClear();
    }
});

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-compare-indicator]')) {
        e.preventDefault();
        const items = getStored();
        if (items.length >= 2) {
            const params = new URLSearchParams();
            items.forEach((item, idx) => params.set('i' + (idx + 1), item.slug));
            window.location.href = '/compare?' + params.toString();
        } else {
            const tray = document.getElementById('compareTray');
            if (tray && items.length > 0) {
                tray.classList.remove('hidden');
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            } else {
                window.location.href = '/institutes';
            }
        }
    }
});

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-print]')) {
        e.preventDefault();
        window.print();
    }
});

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-share]')) {
        e.preventDefault();
        copyShareUrl();
    }
});

document.addEventListener('change', (e) => {
    if (e.target.closest('[data-toggle-identical]')) {
        toggleIdentical();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    renderTray();
    updateButtonStates();
});
