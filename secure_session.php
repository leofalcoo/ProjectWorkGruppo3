<?php
/**
 * Configurazione Sicurezza Sessioni - Tenuta Manarese
 * Implementa le misure di sicurezza per prevenire session hijacking
 */

// Configurazione sicura dei cookie di sessione
function configureSecureSessions() {
    // Prevenzione session hijacking: rigenerazione ID sessione
    if (session_status() === PHP_SESSION_NONE) {
        // Configurazione cookie sicuri PRIMA di iniziare la sessione
        ini_set('session.cookie_httponly', 1);    // HttpOnly: previene accesso JavaScript
        ini_set('session.cookie_secure', 1);      // Secure: solo HTTPS (da abilitare in produzione)
        ini_set('session.use_strict_mode', 1);    // Strict mode: rifiuta ID non inizializzati
        ini_set('session.cookie_samesite', 'Strict'); // SameSite: protezione CSRF
        
        // Configurazioni aggiuntive di sicurezza
        ini_set('session.use_only_cookies', 1);   // Solo cookie, no URL
        ini_set('session.entropy_length', 32);    // Entropia alta per ID sessione
        ini_set('session.hash_function', 'sha256'); // Hash sicuro per ID
        
        // Configurazione durata sessione (30 minuti di inattività)
        ini_set('session.gc_maxlifetime', 1800);
        ini_set('session.cookie_lifetime', 0);    // Scade alla chiusura browser
        
        // Avvia la sessione
        session_start();
        
        // Rigenerazione periodica dell'ID sessione (ogni 15 minuti)
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 900) { // 15 minuti
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
        
        // Verifica IP per prevenire session hijacking (opzionale, può causare problemi con proxy/NAT)
        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        } elseif ($_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            // IP cambiato, possibile hijacking - per ora solo log, non blocco
            error_log("SECURITY WARNING: Session IP changed from {$_SESSION['user_ip']} to " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
        
        // Verifica User-Agent per prevenire session hijacking
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            // User-Agent cambiato, possibile hijacking - per ora solo log
            error_log("SECURITY WARNING: Session User-Agent changed");
        }
    }
}

/**
 * Funzione per distruggere la sessione in modo sicuro
 */
function destroySecureSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Cancella tutte le variabili di sessione
        $_SESSION = array();
        
        // Cancella il cookie di sessione
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Distruggi la sessione
        session_destroy();
    }
}

/**
 * Funzione per validare la sessione corrente
 */
function validateSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }
    
    // Controlla se la sessione è scaduta (30 minuti di inattività)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        destroySecureSession();
        return false;
    }
    
    // Aggiorna timestamp ultima attività
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Funzione per il login sicuro
 */
function secureLogin($user_id, $user_data) {
    // Rigenerazione ID sessione per prevenire session fixation
    session_regenerate_id(true);
    
    // Imposta variabili di sessione sicure
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_nome'] = $user_data['nome'] ?? '';
    $_SESSION['user_cognome'] = $user_data['cognome'] ?? '';
    $_SESSION['user_email'] = $user_data['email'] ?? '';
    $_SESSION['user_ruolo'] = $user_data['ruolo'] ?? 'cliente';
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Log dell'accesso sicuro
    error_log("SECURE LOGIN: User {$user_id} logged in from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * Funzione per il logout sicuro
 */
function secureLogout() {
    if (isset($_SESSION['user_id'])) {
        error_log("SECURE LOGOUT: User {$_SESSION['user_id']} logged out");
    }
    
    destroySecureSession();
}

/**
 * Middleware di sicurezza per le pagine protette
 */
function requireSecureLogin($redirect_to = 'login.php') {
    if (!validateSession() || !isLoggedIn()) {
        header("Location: $redirect_to");
        exit;
    }
}

/**
 * Middleware di sicurezza per le pagine admin
 */
function requireAdminAccess($redirect_to = 'index.php') {
    requireSecureLogin();
    if (!isAdmin()) {
        header("Location: $redirect_to");
        exit;
    }
}
?>
