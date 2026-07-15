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
    const slugs = items.map(i => i.slug).filter(Boolean).join('-vs-');
    if (items.length >= 2) {
        window.location.href = `/compare/${slugs}`;
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
        const slugs = items.map(i => i.slug).join('-vs-');
        btn.onclick = () => { window.location.href = `/compare/${slugs}`; };
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

window.compareAdd = function (uuid, slug, name) {
    let items = getStored();
    if (items.length >= MAX_ITEMS) {
        alert('Maximum ' + MAX_ITEMS + ' institutes can be compared.');
        return;
    }
    if (items.find(i => i.uuid === uuid)) {
        alert('Institute already in comparison.');
        return;
    }
    items.push({ uuid, slug, name });
    setStored(items);
    renderTray();
    updateButtonStates();
};

window.compareRemove = function (uuid) {
    let items = getStored().filter(i => i.uuid !== uuid);
    setStored(items);

    const isOnComparePage = window.location.pathname.startsWith('/compare/');
    if (isOnComparePage) {
        navigateCompare(items);
    } else {
        renderTray();
        updateButtonStates();
    }
};

window.compareClear = function () {
    setStored([]);
    const isOnComparePage = window.location.pathname.startsWith('/compare/');
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

    if (!uuid) return;

    const items = getStored();
    const existing = items.findIndex(i => i.uuid === uuid);

    if (existing !== -1) {
        compareRemove(uuid);
    } else {
        compareAdd(uuid, slug, name);
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
            window.location.href = `/compare/${items.map(i => i.slug).join('-vs-')}`;
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
