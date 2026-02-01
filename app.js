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
let insertAfterIndex = null; // For inserting new link after a specific separator

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

    // Group links into sections (separated by separators)
    // Each section ends with an "Add" card
    let sectionStartIndex = 0;

    for (let i = 0; i <= state.links.length; i++) {
        const link = state.links[i];
        const isEnd = i === state.links.length;
        const isSeparator = link && link.type === 'separator';

        if (isSeparator || isEnd) {
            // Render "Add Link" card at the end of this section (before this separator)
            const addCard = createAddCard(i - 1); // Insert after the last item of this section
            portalGrid.appendChild(addCard);

            // If this is a separator, render it
            if (isSeparator) {
                const card = createLinkCard(link, i);
                portalGrid.appendChild(card);
            }

            // If this is the end, also add "Add Section" button
            if (isEnd) {
                const addSepCard = createAddSeparatorCard();
                portalGrid.appendChild(addSepCard);
            }

            sectionStartIndex = i + 1;
        } else {
            // Regular link
            const card = createLinkCard(link, i);
            portalGrid.appendChild(card);
        }
    }
}

// Create "Add Link" card for a section
function createAddCard(insertAfterIdx) {
    const card = document.createElement('div');
    card.className = 'link-card add-card';
    card.innerHTML = `
        <i class="fa-solid fa-plus add-icon"></i>
        <span class="add-text">Add Link</span>
    `;
    card.onclick = () => {
        insertAfterIndex = insertAfterIdx;
        openEditModal(-1);
    };
    return card;
}

// Create "Add Section" card
function createAddSeparatorCard() {
    const card = document.createElement('div');
    card.className = 'link-card add-card add-separator-card';
    card.innerHTML = `
        <i class="fa-solid fa-layer-group add-icon"></i>
        <span class="add-text">Add Section</span>
    `;
    card.onclick = () => openSeparatorModal(-1); // -1 means new separator
    return card;
}

function addSeparator() {
    openSeparatorModal(-1);
}

function createLinkCard(link, index) {
    const card = document.createElement('div');
    card.className = 'link-card filled';
    card.draggable = true; // Enable dragging
    card.dataset.index = index;

    const isSeparator = link.type === 'separator';

    if (isSeparator) {
        card.className += ' separator-card';
        const themeStyle = link.themeColor ? `style="--separator-color: ${link.themeColor}"` : '';
        const titleStyle = link.themeColor ? `style="color: ${link.themeColor}"` : '';
        const titleText = link.title ? `<span class="separator-title" ${titleStyle}>${link.title}</span>` : '';
        const colorIndicator = link.themeColor ? `<span class="color-indicator" style="background: ${link.themeColor}"></span>` : '';
        card.innerHTML = `
            <div class="card-actions">
                <button class="edit" title="Edit Title & Color"><i class="fa-solid fa-pen"></i></button>
                <button class="delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
            ${colorIndicator}
            ${titleText}
        `;
        if (!link.title) {
            card.classList.add('no-title');
        }
    } else {
        const iconStyle = link.iconColor ? `style="color: ${link.iconColor}"` : '';
        card.innerHTML = `
            <i class="fa-solid ${link.icon || 'fa-link'} icon" ${iconStyle}></i>
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
            if (isSeparator) {
                openSeparatorModal(index);
            } else {
                openEditModal(index);
            }
        };
    }

    const deleteBtn = card.querySelector('.delete');
    if (deleteBtn) {
        deleteBtn.onclick = (e) => {
            e.stopPropagation();
            const confirmMsg = isSeparator ? 'Delete this separator?' : 'Delete this link?';
            if (confirm(confirmMsg)) {
                state.links.splice(index, 1);
                renderGrid();
            }
        };
    }

    card.onclick = () => {
        if (isSeparator) {
            openSeparatorModal(index);
        } else {
            openEditModal(index);
        }
    };

    return card;
}

// ============================================================
// MODAL
// ============================================================
function openEditModal(index) {
    editingIndex = index;
    const link = index >= 0 ? state.links[index] : { label: '', url: '', icon: 'fa-link', iconColor: '' };

    document.getElementById('link-label').value = link.label || '';
    document.getElementById('link-url').value = link.url || '';
    document.getElementById('link-icon').value = link.icon || 'fa-link';

    // Icon color
    const defaultColor = '#2f81f7';
    const iconColor = link.iconColor || defaultColor;
    document.getElementById('link-icon-color').value = iconColor;
    document.getElementById('link-icon-color-text').value = link.iconColor || '';

    // Sync color inputs
    const colorPicker = document.getElementById('link-icon-color');
    const colorText = document.getElementById('link-icon-color-text');

    colorPicker.onchange = () => {
        colorText.value = colorPicker.value;
        updateIconPreview(document.getElementById('link-icon').value, colorPicker.value);
    };

    colorText.onchange = () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(colorText.value)) {
            colorPicker.value = colorText.value;
            updateIconPreview(document.getElementById('link-icon').value, colorText.value);
        }
    };

    // Render icon grid
    renderIconGrid(link.icon || 'fa-link');

    // Update preview
    updateIconPreview(link.icon || 'fa-link', iconColor);

    // Show/hide delete button (hide for new links)
    const deleteBtn = document.getElementById('modal-delete-btn');
    if (deleteBtn) {
        deleteBtn.style.display = index >= 0 ? 'block' : 'none';
    }

    modal.classList.add('active');
}

function closeModal() {
    modal.classList.remove('active');
    editingIndex = null;
    insertAfterIndex = null; // Reset insert position
}

function deleteCurrentLink() {
    if (editingIndex !== null && editingIndex >= 0) {
        if (confirm('Delete this link?')) {
            state.links.splice(editingIndex, 1);
            closeModal();
            renderGrid();
        }
    }
}

// ============================================================
// SEPARATOR MODAL
// ============================================================
let editingSeparatorIndex = null;
const separatorModal = document.getElementById('separator-modal');

function openSeparatorModal(index) {
    editingSeparatorIndex = index;
    const separator = index >= 0 ? state.links[index] : { type: 'separator', title: '', themeColor: '' };

    // Set modal title
    document.getElementById('separator-modal-title').textContent = index >= 0 ? 'Edit Section' : 'Add Section';

    // Fill form
    document.getElementById('separator-title').value = separator.title || '';

    const defaultColor = '#2f81f7';
    const themeColor = separator.themeColor || defaultColor;
    document.getElementById('separator-color').value = themeColor;
    document.getElementById('separator-color-text').value = separator.themeColor || '';

    // Show/hide delete button
    const deleteBtn = document.getElementById('separator-delete-btn');
    if (deleteBtn) {
        deleteBtn.style.display = index >= 0 ? 'block' : 'none';
    }

    // Setup color sync
    const colorPicker = document.getElementById('separator-color');
    const colorText = document.getElementById('separator-color-text');

    colorPicker.onchange = () => {
        colorText.value = colorPicker.value;
        updateSeparatorPreview();
    };

    colorText.onchange = () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(colorText.value)) {
            colorPicker.value = colorText.value;
        }
        updateSeparatorPreview();
    };

    document.getElementById('separator-title').oninput = updateSeparatorPreview;

    updateSeparatorPreview();
    separatorModal.classList.add('active');
}

function closeSeparatorModal() {
    separatorModal.classList.remove('active');
    editingSeparatorIndex = null;
}

function updateSeparatorPreview() {
    const title = document.getElementById('separator-title').value || 'SECTION TITLE';
    const colorText = document.getElementById('separator-color-text').value;
    const color = colorText && /^#[0-9A-Fa-f]{6}$/.test(colorText) ? colorText : '#2f81f7';

    const preview = document.getElementById('separator-preview');
    const lineEl = preview.querySelector('.separator-line');
    if (lineEl) lineEl.style.background = color;

    const textEl = document.getElementById('separator-preview-text');
    textEl.textContent = title.toUpperCase();
    textEl.style.color = color;
}

function resetSeparatorColor() {
    document.getElementById('separator-color').value = '#2f81f7';
    document.getElementById('separator-color-text').value = '';
    updateSeparatorPreview();
}

function saveSeparator() {
    const title = document.getElementById('separator-title').value.trim();
    const colorText = document.getElementById('separator-color-text').value.trim();

    const separator = { type: 'separator', title: title };
    if (colorText && /^#[0-9A-Fa-f]{6}$/.test(colorText)) {
        separator.themeColor = colorText;
    }

    if (editingSeparatorIndex >= 0) {
        state.links[editingSeparatorIndex] = separator;
    } else {
        state.links.push(separator);
    }

    closeSeparatorModal();
    renderGrid();
}

function deleteCurrentSeparator() {
    if (editingSeparatorIndex !== null && editingSeparatorIndex >= 0) {
        if (confirm('Delete this section?')) {
            state.links.splice(editingSeparatorIndex, 1);
            closeSeparatorModal();
            renderGrid();
        }
    }
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
            const currentColor = document.getElementById('link-icon-color').value;
            updateIconPreview(icon, currentColor);
        };
        grid.appendChild(div);
    });
}

function updateIconPreview(icon, color) {
    const colorStyle = color ? `style="color: ${color}"` : '';
    document.getElementById('icon-preview').innerHTML = `
        <i class="fa-solid ${icon}" ${colorStyle}></i>
        <span>${icon}</span>
    `;
}

function resetIconColor() {
    document.getElementById('link-icon-color').value = '#2f81f7';
    document.getElementById('link-icon-color-text').value = '';
    updateIconPreview(document.getElementById('link-icon').value, '#2f81f7');
}

function saveLink() {
    const label = document.getElementById('link-label').value.trim();
    const url = document.getElementById('link-url').value.trim();
    const icon = document.getElementById('link-icon').value.trim() || 'fa-link';
    const iconColorText = document.getElementById('link-icon-color-text').value.trim();
    const iconColor = iconColorText || null; // Only save if custom color is set

    if (!label) {
        alert('Label is required');
        return;
    }

    const link = { label, url, icon };
    if (iconColor) {
        link.iconColor = iconColor;
    }

    if (editingIndex >= 0) {
        state.links[editingIndex] = link;
    } else {
        // New link
        if (insertAfterIndex !== null) {
            // Insert after the specified index (-1 means at position 0)
            const insertPos = insertAfterIndex + 1;
            state.links.splice(insertPos, 0, link);
            insertAfterIndex = null; // Reset
        } else {
            // Append to end
            state.links.push(link);
        }
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
