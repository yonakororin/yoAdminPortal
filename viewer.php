<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../shared/theme.css">
    <style>
        /* Use theme.css variables */
        :root {
            --bg: var(--theme-bg, #0d1117);
            --bg-card: var(--theme-bg-card, #161b22);
            --bg-hover: var(--theme-bg-hover, #20262d);
            --border: var(--theme-border, #30363d);
            --text: var(--theme-text, #e6edf3);
            --text-muted: var(--theme-text-muted, #8b949e);
            --primary: var(--theme-primary, #2f81f7);
            --primary-hover: var(--theme-primary-hover, #1f6feb);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header {
            background: var(--theme-header-bg, var(--bg-card));
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .portal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .portal-title:hover {
            opacity: 0.8;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-menu-btn {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .user-menu-btn:hover {
            background: var(--bg-card);
        }
        
        .user-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-top: 0.5rem;
            min-width: 200px;
            display: none;
            z-index: 100;
        }
        
        .user-menu-dropdown.active {
            display: block;
        }
        
        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .user-menu-item:hover {
            background: var(--bg);
        }
        
        .user-menu-item.theme-select select {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        
        main {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 2rem;
        }
        
        .portal-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1.5rem;
            max-width: 1200px;
            width: 100%;
        }
        
        .link-card {
            aspect-ratio: 1;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s ease;
            padding: 1.5rem;
        }
        
        .link-card:hover {
            border-color: var(--primary);
            background: rgba(47, 129, 247, 0.1);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(47, 129, 247, 0.2);
        }
        
        .link-card .icon {
            font-size: 3rem;
            color: var(--primary);
            transition: transform 0.3s;
        }
        
        .link-card:hover .icon {
            transform: scale(1.1);
        }
        
        .link-card .label {
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            word-break: break-word;
        }
        
        .empty-message {
            text-align: center;
            color: var(--text-muted);
            font-size: 1.2rem;
        }
        
        @media (max-width: 1024px) {
            .portal-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .portal-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .portal-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Separator for Viewer */
        .separator-line {
            grid-column: 1 / -1;
            height: 1px;
            background: var(--border);
            margin: 1rem 0;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <header>
        <div class="portal-title" id="portal-title" onclick="editTitle()" title="Click to edit">Portal</div>
        <div class="user-menu">
            <button class="user-menu-btn" onclick="toggleUserMenu()">
                <i class="fa-solid fa-user"></i>
                <?php echo htmlspecialchars($_SESSION['user']); ?>
                <i class="fa-solid fa-chevron-down" style="font-size:0.6rem;"></i>
            </button>
            <div class="user-menu-dropdown" id="user-dropdown">

                <a href="../yoSSO/change_password.php?redirect_uri=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="user-menu-item">
                    <i class="fa-solid fa-key"></i>
                    <span>Change Password</span>
                </a>
                <a href="logout.php?next=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="user-menu-item">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>
    
    <main>
        <div id="portal-grid" class="portal-grid">
            <!-- Links will be rendered by JS -->
        </div>
    </main>
    
    <script>
        async function init() {
            const urlParams = new URLSearchParams(window.location.search);
            const configFile = urlParams.get('config') || 'portal_config.json';
            
            try {
                const res = await fetch(`api.php?file=${encodeURIComponent(configFile)}`);
                if (res.ok) {
                    const data = await res.json();
                    renderPortal(data);
                }
            } catch (e) {
                console.error('Failed to load config:', e);
            }
        }
        
        function renderPortal(data) {
            // Store config for editing
            currentConfig = data;
            configFile = new URLSearchParams(window.location.search).get('config') || 'portal_config.json';
            
            // Set title
            document.getElementById('portal-title').textContent = data.title || 'Portal';
            document.title = data.title || 'Portal';
            
            // Render links
            const grid = document.getElementById('portal-grid');
            const links = data.links || [];
            
            if (links.length === 0) {
                grid.innerHTML = '<div class="empty-message">No links configured. Use the builder to add links.</div>';
                return;
            }
            
            grid.innerHTML = '';
            
            // Filter and process links visibility
            const perms = window.userPermissions || [];
            const isAdmin = perms.includes('*');

            // 1. Determine visibility of content links
            const processedLinks = links.map(link => {
                if (link.type === 'separator') {
                    return { type: 'separator', visible: true }; // Initially assume visible
                }
                const isVisible = isAdmin || perms.includes(link.url);
                return { ...link, visible: isVisible };
            });

            // 2. Determine visibility of separators based on section content
            let hasVisibleContentInCurrentSection = false;
            
            for (let i = 0; i < processedLinks.length; i++) {
                const link = processedLinks[i];
                if (link.type === 'separator') {
                    // If the section before this separator has no visible content, 
                    // hide this separator (collapse the empty row)
                    // Also, if it's the first element, hide it
                    if (!hasVisibleContentInCurrentSection) {
                        link.visible = false; 
                    }
                    hasVisibleContentInCurrentSection = false; // Reset for next section
                } else {
                    if (link.visible) {
                        hasVisibleContentInCurrentSection = true;
                    }
                }
            }

            // 3. Render
            processedLinks.forEach(link => {
                if (!link.visible) return;

                if (link.type === 'separator') {
                    const sep = document.createElement('div');
                    sep.className = 'separator-line';
                    grid.appendChild(sep);
                    return;
                }

                const card = document.createElement('a');
                card.className = 'link-card';
                card.href = link.url || '#';
                
                // Use a derived target name to reuse existing tabs
                const targetName = 'yoPortal_' + (link.url || '').replace(/[^a-zA-Z0-9]/g, '');
                card.target = targetName;
                
                card.innerHTML = `
                    <i class="fa-solid ${link.icon || 'fa-link'} icon"></i>
                    <span class="label">${link.label || 'Link'}</span>
                `;
                grid.appendChild(card);
            });
        }
        
        function toggleUserMenu() {
            document.getElementById('user-dropdown').classList.toggle('active');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', (e) => {
            const userMenu = document.querySelector('.user-menu');
            if (userMenu && !userMenu.contains(e.target)) {
                document.getElementById('user-dropdown').classList.remove('active');
            }
        });
        
        // Store current config for editing
        let currentConfig = { title: 'Portal', links: [] };
        let configFile = 'portal_config.json';
        
        function editTitle() {
            const currentTitle = document.getElementById('portal-title').textContent;
            const newTitle = prompt('Enter new title:', currentTitle);
            if (newTitle !== null && newTitle.trim() !== '') {
                currentConfig.title = newTitle.trim();
                document.getElementById('portal-title').textContent = currentConfig.title;
                document.title = currentConfig.title;
                saveConfig();
            }
        }
        
        async function saveConfig() {
            try {
                const formData = new FormData();
                formData.append('filename', configFile);
                formData.append('config', JSON.stringify(currentConfig));
                
                const res = await fetch('api.php', { method: 'POST', body: formData });
                const json = await res.json();
                
                if (!json.success) {
                    console.error('Save failed:', json.message);
                }
            } catch (e) {
                console.error('Error saving:', e);
            }
        }
        
        document.addEventListener('DOMContentLoaded', init);
    </script>
<?php
// Note: $permissions is already set by auth.php (required at top of file)
// No need to reload here

// Load Portal Config for Theme
$config_param = isset($_GET['config']) ? $_GET['config'] : 'portal_config.json';
// Security: only allow .json and basic sanitization
$config_path = $config_param;
if (!preg_match('/^(\/|[a-zA-Z]:)/', $config_param)) {
    $config_path = __DIR__ . '/' . $config_param;
}
$config_path = realpath($config_path) ?: $config_path;

$portal_config = [];
if (file_exists($config_path)) {
    $decoded = json_decode(file_get_contents($config_path), true);
    if (is_array($decoded)) {
        $portal_config = $decoded;
    }
}

?>
    <script>
        window.currentUser = "<?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : '' ?>";
        window.userPermissions = <?= json_encode($permissions) ?>;
        window.mngConfig = <?= json_encode([
            'target_env' => $portal_config['target_env'] ?? (isset($_GET['env']) ? str_replace('web-', '', $_GET['env']) : 'dev'),
            'base_color' => $portal_config['base_color'] ?? null,
            'debug_path' => $config_path
        ]) ?>;
    </script>
    <script src="../shared/theme.js"></script>


    <script>
        // Override renderPortal to apply permissions
        const originalRenderPortal = renderPortal;
        
        // We modify the renderPortal function in the script block above, 
        // OR we can just inject logic. Since I am replacing the file content,
        // let's update the renderPortal function directly in the previous tool call blocks
        // but here I am appending/modifying the end of the file.
        // Actually, it is better to modify the renderPortal function definition itself.
    </script>
</body>
</html>
