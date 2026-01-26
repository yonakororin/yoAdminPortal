<?php
// Shared session configuration
// Ensure session cookie is accessible across all tools under the same domain

// Only set params if session hasn't started
if (session_status() == PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    // Default to root path '/' so all apps (portal, builder, sso) share the session
    session_set_cookie_params(
        $cookieParams["lifetime"],
        '/', 
        $cookieParams["domain"], 
        $cookieParams["secure"], 
        $cookieParams["httponly"]
    );
    
    // Use a common session name if desired, but PHPSESSID is fine if shared.
    // session_name('MNGTOOLS_SESS');
}
?>
