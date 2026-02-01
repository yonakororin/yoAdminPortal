// yoAdminPortal - App.js
// ポータルビルダーのメインスクリプト

const state = {
    title: 'Portal',
    links: [],
    targetFile: 'portal_config.json'
};

// DOM Elements
const portalGrid = document.getElementById('portal-grid');
const portalTitleInput = document.getElementById('portal-title');
const fileInput = document.getElementById('file-input');
const modal = document.getElementById('edit-modal');

// Common Icons - Expanded list
const commonIcons = [
    // Navigation & UI
    'fa-home', 'fa-house', 'fa-bars', 'fa-grip', 'fa-ellipsis', 'fa-arrow-right', 'fa-arrow-left',
    'fa-chevron-right', 'fa-chevron-left', 'fa-angles-right', 'fa-angles-left',

    // Analytics & Charts
    'fa-chart-line', 'fa-chart-bar', 'fa-chart-pie', 'fa-chart-area', 'fa-chart-column',
    'fa-chart-simple', 'fa-square-poll-vertical', 'fa-signal',

    // Users & People
    'fa-user', 'fa-users', 'fa-user-plus', 'fa-user-minus', 'fa-user-cog', 'fa-user-shield',
    'fa-user-tie', 'fa-user-group', 'fa-people-group', 'fa-id-card', 'fa-address-book',

    // Settings & Tools
    'fa-cog', 'fa-gear', 'fa-gears', 'fa-sliders', 'fa-wrench', 'fa-screwdriver-wrench',
    'fa-tools', 'fa-hammer', 'fa-toolbox',

    // Data & Database
    'fa-database', 'fa-server', 'fa-hard-drive', 'fa-hdd', 'fa-memory', 'fa-microchip',
    'fa-cloud', 'fa-cloud-arrow-up', 'fa-cloud-arrow-down',

    // Files & Folders
    'fa-file', 'fa-file-lines', 'fa-file-code', 'fa-file-excel', 'fa-file-pdf', 'fa-file-word',
    'fa-file-zipper', 'fa-file-import', 'fa-file-export', 'fa-folder', 'fa-folder-open',
    'fa-folder-plus', 'fa-folder-tree', 'fa-box-archive',

    // Communication
    'fa-envelope', 'fa-envelope-open', 'fa-message', 'fa-comments', 'fa-comment',
    'fa-bell', 'fa-phone', 'fa-headset', 'fa-inbox',

    // Time & Calendar
    'fa-calendar', 'fa-calendar-days', 'fa-calendar-check', 'fa-calendar-plus',
    'fa-clock', 'fa-stopwatch', 'fa-hourglass', 'fa-history',

    // Actions
    'fa-search', 'fa-magnifying-glass', 'fa-filter', 'fa-sort', 'fa-download', 'fa-upload',
    'fa-share', 'fa-share-nodes', 'fa-copy', 'fa-paste', 'fa-trash', 'fa-pen', 'fa-edit',
    'fa-plus', 'fa-minus', 'fa-xmark', 'fa-check', 'fa-rotate', 'fa-refresh',

    // Links & Web
    'fa-link', 'fa-unlink', 'fa-globe', 'fa-earth-americas', 'fa-wifi', 'fa-rss',
    'fa-at', 'fa-hashtag', 'fa-qrcode', 'fa-barcode',

    // Security
    'fa-lock', 'fa-unlock', 'fa-key', 'fa-shield', 'fa-shield-halved', 'fa-user-lock',
    'fa-fingerprint', 'fa-eye', 'fa-eye-slash',

    // Development
    'fa-code', 'fa-terminal', 'fa-bug', 'fa-code-branch', 'fa-code-commit',
    'fa-code-merge', 'fa-code-pull-request', 'fa-laptop-code',

    // Objects
    'fa-cube', 'fa-cubes', 'fa-box', 'fa-boxes-stacked', 'fa-sitemap', 'fa-list',
    'fa-list-check', 'fa-table', 'fa-table-cells', 'fa-layer-group',

    // Finance
    'fa-wallet', 'fa-credit-card', 'fa-money-bill', 'fa-money-bill-wave', 'fa-coins',
    'fa-piggy-bank', 'fa-receipt', 'fa-calculator', 'fa-percent',

    // Misc
    'fa-star', 'fa-heart', 'fa-bookmark', 'fa-flag', 'fa-tag', 'fa-tags',
    'fa-lightbulb', 'fa-bolt', 'fa-fire', 'fa-rocket', 'fa-paper-plane',
    'fa-trophy', 'fa-medal', 'fa-crown', 'fa-gem', 'fa-gift',

    // Arrows & Directions
    'fa-arrow-up', 'fa-arrow-down', 'fa-arrow-up-right-from-square', 'fa-up-right-from-square',
    'fa-circle-arrow-right', 'fa-circle-arrow-left', 'fa-rotate-right',

    // Media
    'fa-image', 'fa-images', 'fa-camera', 'fa-video', 'fa-music', 'fa-headphones',
    'fa-play', 'fa-pause', 'fa-stop', 'fa-volume-high',

    // Location
    'fa-location-dot', 'fa-map', 'fa-map-location-dot', 'fa-compass', 'fa-building',
    'fa-city', 'fa-store', 'fa-warehouse',

    // Status
    'fa-circle-check', 'fa-circle-xmark', 'fa-circle-info', 'fa-circle-exclamation',
    'fa-triangle-exclamation', 'fa-circle-question', 'fa-question'
];

let editingIndex = null;
let draggedIndex = null;

// ============================================================
// INITIALIZATION
// ============================================================
async function init() {
    await loadConfig();
    renderGrid();
    setupEventListeners();
}

async function loadConfig() {
    const urlParams = new URLSearchParams(window.location.search);
    const urlConfig = urlParams.get('config');

    // state.targetFile が既に設定されている場合はそれを使用
    // そうでなければ URL パラメータ、localStorage、デフォルト値の順で取得
    const file = state.targetFile || urlConfig || localStorage.getItem('yoPortalTargetFile') || 'portal_config.json';

    state.targetFile = file;
    fileInput.value = file;
    localStorage.setItem('yoPortalTargetFile', file);

    try {
        const res = await fetch(`api.php?file=${encodeURIComponent(file)}`);
        if (res.ok) {
            const data = await res.json();
            state.title = data.title || 'Portal';
            state.links = data.links || [];
            portalTitleInput.value = state.title;
        }
    } catch (e) {
        console.error('Failed to load config:', e);
    }
}

async function saveConfig() {
    try {
        const configData = {
            title: state.title,
            links: state.links
        };

        const formData = new FormData();
        formData.append('filename', state.targetFile);
        formData.append('config', JSON.stringify(configData));

        const res = await fetch('api.php', { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            alert('Saved!');
        } else {
            alert('Save failed: ' + (json.message || 'Unknown error'));
        }
    } catch (e) {
        console.error(e);
        alert('Error saving.');
    }
}

// ============================================================
// RENDERING
// ============================================================
function renderGrid() {
    portalGrid.innerHTML = '';

    // Render existing links
    state.links.forEach((link, index) => {
        const card = createLinkCard(link, index);
        portalGrid.appendChild(card);
    });

    // Add buttons
    const addContainer = document.createElement('div');
    addContainer.className = 'link-card';
    addContainer.style.border = '2px dashed var(--border)';
    addContainer.style.background = 'transparent';
    addContainer.style.flexDirection = 'column';
    addContainer.style.gap = '8px';
    addContainer.style.justifyContent = 'center';

    // Add Link Button
    const addLinkBtn = document.createElement('div');
    addLinkBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Link';
    addLinkBtn.className = 'btn btn-secondary';
    addLinkBtn.style.width = '100%';
    addLinkBtn.onclick = () => openEditModal(-1);

    // Add Separator Button
    const addSepBtn = document.createElement('div');
    addSepBtn.innerHTML = '<i class="fa-solid fa-minus"></i> Add Line Break';
    addSepBtn.className = 'btn btn-secondary';
    addSepBtn.style.width = '100%';
    addSepBtn.onclick = () => addSeparator();

    addContainer.appendChild(addLinkBtn);
    addContainer.appendChild(addSepBtn);

    portalGrid.appendChild(addContainer);
}

function addSeparator() {
    state.links.push({ type: 'separator' });
    renderGrid();
}

function createLinkCard(link, index) {
    const card = document.createElement('div');
    card.className = 'link-card filled';
    card.draggable = true; // Enable dragging
    card.dataset.index = index;

    const isSeparator = link.type === 'separator';

    if (isSeparator) {
        card.className += ' separator-card';
        card.innerHTML = `
            <div class="card-actions">
                <button class="delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
    } else {
        card.innerHTML = `
            <div class="card-actions">
                <button class="edit" title="Edit"><i class="fa-solid fa-pen"></i></button>
                <button class="delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
            <i class="fa-solid ${link.icon || 'fa-link'} icon"></i>
            <span class="label">${link.label || 'Link'}</span>
        `;
    }

    // --- Drag and Drop Handlers ---
    // --- Drag and Drop Handlers ---
    card.addEventListener('dragstart', (e) => {
        draggedIndex = index;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', index);
        // Delay adding class so the drag image is taken from the normal state
        setTimeout(() => card.classList.add('dragging'), 0);
    });

    card.addEventListener('dragend', () => {
        card.classList.remove('dragging');
        document.querySelectorAll('.link-card').forEach(c => c.classList.remove('drag-over'));
        draggedIndex = null;
    });

    card.addEventListener('dragover', (e) => {
        e.preventDefault(); // Necessary to allow dropping
        e.dataTransfer.dropEffect = 'move';
        if (draggedIndex !== index) {
            card.classList.add('drag-over');
        }
    });

    card.addEventListener('dragleave', (e) => {
        // Prevent flickering when moving over child elements
        if (card.contains(e.relatedTarget)) return;
        card.classList.remove('drag-over');
    });

    card.addEventListener('drop', (e) => {
        e.preventDefault();
        card.classList.remove('drag-over');

        // Ensure index types match (though strict equality with null handles initialization)
        if (draggedIndex === null || draggedIndex === index) return;

        // Perform reordering
        const movedItem = state.links[draggedIndex];

        // Remove from old position
        state.links.splice(draggedIndex, 1);

        // Calculate new index adjusting for removal if moving down
        // If draggedIndex < index, the target index shifts down by 1 after removal
        // However, 'index' is the original index of the Drop Target.
        // Example: [A, B, C], Move A(0) to C(2). Remove A -> [B, C]. C is now at 1.
        // If we want to insert 'After' C (originally 2, now 1), we insert at 2.
        // If we want to insert 'Before' C (originally 2, now 1), we insert at 1.
        // Let's stick to "Insert Before Target" relative to the view
        let insertAt = index;
        if (draggedIndex < index) {
            insertAt = index - 1;
        }

        // Boundary check
        if (insertAt < 0) insertAt = 0;

        state.links.splice(insertAt, 0, movedItem);

        renderGrid();
        // Optional: Auto-save or wait for user to click save
    });
    // ------------------------------
    // ------------------------------

    const editBtn = card.querySelector('.edit');
    if (editBtn) {
        editBtn.onclick = (e) => {
            e.stopPropagation();
            openEditModal(index);
        };
    }

    card.querySelector('.delete').onclick = (e) => {
        e.stopPropagation();
        if (confirm('Delete this link?')) {
            state.links.splice(index, 1);
            renderGrid();
        }
    };

    card.onclick = () => {
        if (!isSeparator) openEditModal(index);
    };

    return card;
}

// ============================================================
// MODAL
// ============================================================
function openEditModal(index) {
    editingIndex = index;
    const link = index >= 0 ? state.links[index] : { label: '', url: '', icon: 'fa-link' };

    document.getElementById('link-label').value = link.label || '';
    document.getElementById('link-url').value = link.url || '';
    document.getElementById('link-icon').value = link.icon || 'fa-link';

    // Render icon grid
    renderIconGrid(link.icon || 'fa-link');

    // Update preview
    updateIconPreview(link.icon || 'fa-link');

    modal.classList.add('active');
}

function closeModal() {
    modal.classList.remove('active');
    editingIndex = null;
}

function renderIconGrid(selectedIcon) {
    const grid = document.getElementById('icon-grid');
    grid.innerHTML = '';

    commonIcons.forEach(icon => {
        const div = document.createElement('div');
        div.className = 'icon-option' + (icon === selectedIcon ? ' selected' : '');
        div.innerHTML = `<i class="fa-solid ${icon}"></i>`;
        div.onclick = () => {
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            div.classList.add('selected');
            document.getElementById('link-icon').value = icon;
            updateIconPreview(icon);
        };
        grid.appendChild(div);
    });
}

function updateIconPreview(icon) {
    document.getElementById('icon-preview').innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${icon}</span>
    `;
}

function saveLink() {
    const label = document.getElementById('link-label').value.trim();
    const url = document.getElementById('link-url').value.trim();
    const icon = document.getElementById('link-icon').value.trim() || 'fa-link';

    if (!label) {
        alert('Label is required');
        return;
    }

    const link = { label, url, icon };

    if (editingIndex >= 0) {
        state.links[editingIndex] = link;
    } else {
        state.links.push(link);
    }

    closeModal();
    renderGrid();
}

// ============================================================
// EVENT LISTENERS
// ============================================================
function setupEventListeners() {
    // Title change
    portalTitleInput.addEventListener('change', () => {
        state.title = portalTitleInput.value;
    });

    // Modal close on overlay click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // Icon input change
    document.getElementById('link-icon').addEventListener('input', (e) => {
        updateIconPreview(e.target.value || 'fa-link');
    });
}

// File operations
async function loadFile() {
    const file = fileInput.value;
    if (!file) return;

    state.targetFile = file;
    localStorage.setItem('yoPortalTargetFile', file);

    // Update URL
    const newUrl = new URL(window.location);
    newUrl.searchParams.set('config', file);
    window.history.pushState({ path: newUrl.href }, '', newUrl.href);

    await loadConfig();
    renderGrid();
}

// User menu toggle
function toggleUserMenu() {
    document.getElementById('user-dropdown').classList.toggle('active');
}

// Close user menu when clicking outside
document.addEventListener('click', (e) => {
    const userMenu = document.querySelector('.user-menu');
    if (!userMenu.contains(e.target)) {
        document.getElementById('user-dropdown').classList.remove('active');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', init);
