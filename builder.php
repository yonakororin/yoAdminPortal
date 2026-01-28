<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>yoAdmin Portal Builder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app">
        <header>
            <div class="brand">
                <i class="fa-solid fa-shapes"></i> Portal Builder
            </div>
            <div class="header-controls">
                <input type="text" id="file-input" placeholder="portal_config.json" value="portal_config.json">
                <button class="btn btn-secondary" onclick="loadFile()">
                    <i class="fa-solid fa-folder-open"></i> Load
                </button>
                <button class="btn btn-primary" onclick="saveConfig()">
                    <i class="fa-solid fa-save"></i> Save
                </button>
                <button class="btn btn-secondary" onclick="openHelp()" title="ヘルプ">
                    <i class="fa-regular fa-circle-question"></i> Help
                </button>
            </div>
            <div class="user-menu">
                <button class="user-menu-btn" onclick="toggleUserMenu()">
                    <i class="fa-solid fa-user"></i>
                    <?php echo htmlspecialchars($_SESSION['user']); ?>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div id="user-dropdown" class="user-menu-dropdown">
                    <a href="../yoSSO/change_password.php?redirect_uri=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="user-menu-item">
                        <i class="fa-solid fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="logout.php?next=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </header>
        
        <main>
            <div class="title-section">
                <label>Portal Title:</label>
                <input type="text" id="portal-title" placeholder="My Portal" value="Portal">
            </div>
            
            <div id="portal-grid" class="portal-grid">
                <!-- Grid will be rendered by JS -->
            </div>
        </main>
    </div>
    
    <!-- Edit Modal -->
    <div id="edit-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Edit Link</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" id="link-label" placeholder="Dashboard">
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="text" id="link-url" placeholder="https://example.com/dashboard">
                    <small>Link destination URL</small>
                </div>
                <div class="form-group">
                    <label>Icon (FontAwesome)</label>
                    <input type="text" id="link-icon" placeholder="fa-home" value="fa-link">
                    <small>e.g. fa-home, fa-chart-line, fa-users</small>
                </div>
                <div class="form-group">
                    <label>Preview</label>
                    <div id="icon-preview" class="icon-preview">
                        <i class="fa-solid fa-link"></i>
                        <span>fa-link</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Select Icon</label>
                    <div id="icon-grid" class="icon-grid">
                        <!-- Icons will be rendered by JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveLink()">Save</button>
            </div>
        </div>
    </div>
    
    <!-- Help Modal -->
    <div id="help-modal" class="modal-overlay" style="display:none;">
        <div class="modal" style="max-width:900px;width:90%;max-height:90vh;">
            <div class="modal-header">
                <h2><i class="fa-regular fa-circle-question"></i> yoAdminPortal ガイド</h2>
                <button class="close-btn" onclick="closeHelpModal()">&times;</button>
            </div>
            <div class="modal-body" style="max-height:calc(90vh - 120px);overflow-y:auto;">
                <div id="help-content" class="markdown-body" style="line-height:1.7;">
                    <p style="color:var(--text-muted);">読み込み中...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Marked.js for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <style>
        /* Markdown styles */
        .markdown-body {
            color: var(--text, #e6edf3);
        }
        .markdown-body h1, .markdown-body h2, .markdown-body h3 {
            border-bottom: 1px solid var(--border, #30363d);
            padding-bottom: 0.5em;
            margin-top: 1.5em;
        }
        .markdown-body h1 { font-size: 1.8em; }
        .markdown-body h2 { font-size: 1.4em; }
        .markdown-body h3 { font-size: 1.2em; }
        .markdown-body table {
            border-collapse: collapse;
            width: 100%;
            margin: 1em 0;
        }
        .markdown-body th, .markdown-body td {
            border: 1px solid var(--border, #30363d);
            padding: 8px 12px;
            text-align: left;
        }
        .markdown-body th {
            background: var(--bg-card, #161b22);
        }
        .markdown-body code {
            background: var(--bg-card, #161b22);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .markdown-body pre {
            background: var(--bg-card, #161b22);
            padding: 1em;
            border-radius: 6px;
            overflow-x: auto;
        }
        .markdown-body pre code {
            background: none;
            padding: 0;
        }
        .markdown-body hr {
            border: none;
            border-top: 1px solid var(--border, #30363d);
            margin: 2em 0;
        }
        .markdown-body ul, .markdown-body ol {
            padding-left: 2em;
        }
        .markdown-body li {
            margin: 0.5em 0;
        }
        .markdown-body a {
            color: var(--primary, #2f81f7);
        }
        .markdown-body blockquote {
            border-left: 4px solid var(--primary, #2f81f7);
            margin: 1em 0;
            padding-left: 1em;
            color: var(--text-muted, #8b949e);
        }
    </style>
    
    <script>
        // Help Modal Functions
        async function openHelp() {
            document.getElementById('help-modal').style.display = 'flex';
            const contentEl = document.getElementById('help-content');
            
            try {
                const res = await fetch('GUIDE.md');
                if (!res.ok) throw new Error('Failed to load guide');
                const text = await res.text();
                contentEl.innerHTML = marked.parse(text);
            } catch (e) {
                contentEl.innerHTML = '<p style="color:#f87171;">ガイドの読み込みに失敗しました: ' + e.message + '</p>';
            }
        }
        
        function closeHelpModal() {
            document.getElementById('help-modal').style.display = 'none';
        }
        
        // Close help modal on overlay click
        document.getElementById('help-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'help-modal') {
                closeHelpModal();
            }
        });
        
        // Close help modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeHelpModal();
            }
        });
    </script>
    
    <script src="app.js"></script>
</body>
</html>
