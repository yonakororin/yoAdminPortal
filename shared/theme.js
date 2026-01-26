/**
 * mngtools Shared Theme System
 * Environment-based styling (No separate dark/light toggle)
 */

(function () {
    'use strict';

    // Environment detection
    function getEnvironment() {
        const params = new URLSearchParams(window.location.search);
        // Priority 1: URL parameter (e.g. ?env=stg or ?target_env=prd)
        let env = params.get('env') || params.get('target_env');
        if (env) {
            env = env.replace('web-', ''); // Handle 'web-stg' -> 'stg'
            return env;
        }

        // Priority 2: Global config from PHP
        if (window.mngConfig && window.mngConfig.target_env) {
            return window.mngConfig.target_env;
        }

        return 'dev';
    }

    // Helper to make a color lighter for header backgrounds
    function getLightversion(hex, opacity = 0.15) {
        if (!hex || !hex.startsWith('#')) return '#f1f5f9';
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    }

    // Helper to determine if text should be white or black based on background color
    function getContrastColor(hex) {
        if (!hex || !hex.startsWith('#')) return '#1e293b'; // Default dark text
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        // Calculate relative luminance (YIQ)
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return (yiq >= 128) ? '#1e293b' : '#ffffff';
    }
    // Helper to darken a color
    function darkenColor(hex, percent) {
        if (!hex || !hex.startsWith('#')) return hex;
        let r = parseInt(hex.slice(1, 3), 16);
        let g = parseInt(hex.slice(3, 5), 16);
        let b = parseInt(hex.slice(5, 7), 16);

        r = Math.max(0, Math.floor(r * (1 - percent / 100)));
        g = Math.max(0, Math.floor(g * (1 - percent / 100)));
        b = Math.max(0, Math.floor(b * (1 - percent / 100)));

        return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    }

    // Apply environment specific styles
    function applyEnvironmentTheme(customConfig) {
        const config = customConfig || window.mngConfig || {};
        const env = getEnvironment(); // Use robust detection
        const baseColor = config.base_color || config.theme_color;

        // Base Light Theme Defaults
        const styles = {
            '--theme-bg': '#f1f5f9',
            '--theme-bg-card': '#ffffff',
            '--theme-bg-hover': '#e2e8f0',
            '--theme-border': '#cbd5e1',
            '--theme-text': '#1e293b',
            '--theme-text-muted': '#64748b'
        };

        // Determine primary color
        let primary = '#3b82f6'; // Default Blue (Dev)
        let primaryHover = '#2563eb';
        let headerBg = '#e0f2fe';
        let headerText = '#1e293b';
        let sidebarBg = '#ffffff';
        let sidebarText = '#1e293b';
        let brandBg = '#f8fafc';
        let brandText = '#1e293b';

        if (baseColor) {
            primary = baseColor;
            primaryHover = baseColor;
            headerBg = baseColor; // Use base color directly for header
            headerText = getContrastColor(baseColor);

            // Sidebar also uses baseColor
            sidebarBg = baseColor;
            sidebarText = getContrastColor(baseColor);

            // Brand (Top section of sidebar) is slightly darker
            brandBg = darkenColor(baseColor, 10);
            brandText = getContrastColor(brandBg);
        } else {
            // Env presets
            if (env === 'stg') {
                primary = '#eab308'; // Yellow
                primaryHover = '#ca8a04';
                headerBg = '#fef9c3';
                headerText = '#1e293b';
            } else if (env === 'prd' || env === 'prod') {
                primary = '#ec4899'; // Pink
                primaryHover = '#db2777';
                headerBg = '#fce7f3';
                headerText = '#1e293b';
            } else {
                // dev default
                primary = '#3b82f6';
                primaryHover = '#2563eb';
                headerBg = '#e0f2fe';
                headerText = '#1e293b';
            }
            sidebarBg = '#ffffff';
            sidebarText = '#1e293b';
            brandBg = '#f8fafc';
            brandText = '#1e293b';
        }

        styles['--theme-primary'] = primary;
        styles['--theme-primary-hover'] = primaryHover;
        styles['--theme-header-bg'] = headerBg;
        styles['--theme-header-text'] = headerText;
        styles['--theme-sidebar-bg'] = sidebarBg;
        styles['--theme-sidebar-text'] = sidebarText;
        styles['--theme-brand-bg'] = brandBg;
        styles['--theme-brand-text'] = brandText;

        // Apply
        const root = document.documentElement;
        // Force light theme
        root.setAttribute('data-theme', 'light');

        for (const [key, value] of Object.entries(styles)) {
            root.style.setProperty(key, value);
        }

        console.log('[mngTheme] Applied:', { env, primary, headerBg, headerText, fromURL: !!new URLSearchParams(window.location.search).get('env') });
    }

    // Expose global API
    window.mngTheme = {
        apply: applyEnvironmentTheme
    };

    // Initialize
    function init() {
        applyEnvironmentTheme();

        // Ensure theme selectors are hidden and forced light
        const style = document.createElement('style');
        style.innerHTML = `
            .theme-selector, .theme-toggle, .theme-select, .user-menu-item.theme-select { display: none !important; }
            body { background-color: var(--theme-bg) !important; color: var(--theme-text) !important; }
        `;
        document.head.appendChild(style);
    }


    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

// =============================================================
// Cross-tab Logout Synchronization
// =============================================================
// =============================================================
// Cross-tab Logout Synchronization
// =============================================================
(function () {
    'use strict';

    // Check session status on visibility change
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            checkSessionAndReload();
        }
    });

    // Also check periodically (e.g., every 30 seconds) just in case
    setInterval(checkSessionAndReload, 30000);

    async function checkSessionAndReload() {
        // Determine type based on URL
        const isSSO = window.location.pathname.includes('/yoSSO/');
        const type = isSSO ? 'sso' : 'tool';

        try {
            // Determine relative path to API
            // Usually shared is ../shared/ from the tool root
            // yoAdminBuilder/builder.php -> ../shared/check_session.php
            // yoAdminPortal/viewer.php -> ../shared/check_session.php
            // yoSSO/index.php -> ../shared/check_session.php

            const res = await fetch('../shared/check_session.php?type=' + type);
            if (res.ok) {
                const data = await res.json();
                const sessionActive = data.logged_in;

                // Get page expectation
                // window.currentUser should be set by the PHP page if it expects a user
                const pageExpectsUser = (typeof window.currentUser !== 'undefined' && window.currentUser !== '');

                // If page expects user but session is gone => Reload (to trigger auth redirect)
                if (pageExpectsUser && !sessionActive) {
                    console.log('[yoAuth] Session expired or logged out elsewhere. Reloading...');
                    window.location.reload();
                }
            }
        } catch (e) {
            console.error('[yoAuth] Failed to check session:', e);
        }
    }
})();
