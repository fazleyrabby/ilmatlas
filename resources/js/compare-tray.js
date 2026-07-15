const STORAGE_KEY = 'ilmatlas_compare';
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

function renderTray() {
    let tray = document.getElementById('compareTray');
    if (!tray) return;

    const items = getStored();

    if (items.length === 0) {
        tray.classList.add('hidden');
        return;
    }

    tray.classList.remove('hidden');

    const list = tray.querySelector('.compare-list');
    const btn = tray.querySelector('.compare-cta');

    list.innerHTML = items.map(item => `
        <span class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
            ${item.name}
            <button onclick="compareRemove('${item.uuid}')" class="text-indigo-400 hover:text-indigo-600">&times;</button>
        </span>
    `).join('');

    btn.disabled = items.length < 2;

    if (items.length >= 2) {
        const slugs = items.map(i => i.slug).join('-vs-');
        btn.onclick = () => { window.location.href = `/compare/${slugs}`; };
    }
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
    renderTray();
    updateButtonStates();
};

window.compareClear = function () {
    setStored([]);
    renderTray();
    updateButtonStates();
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
            btn.onclick = (e) => {
                e.preventDefault();
                compareRemove(uuid);
            };
        } else {
            const inCompareText = 'Remove from Compare';
            if (btn.textContent === inCompareText) {
                btn.click = null;
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderTray();
    updateButtonStates();
});
