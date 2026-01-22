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
    
    <script src="app.js"></script>
</body>
</html>
