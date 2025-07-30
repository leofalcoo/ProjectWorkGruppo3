<?php
/**
 * Configurazione Template per Tenuta Manarese E-commerce
 * ISTRUZIONI: Copia questo file come config.php e inserisci i tuoi dati reali
 */

// ===========================================
// CONFIGURAZIONE DATABASE
// ===========================================

// Database connection settings
define('DB_HOST', 'localhost');           // Il tuo host database
define('DB_NAME', 'your_database_name');  // Nome del tuo database
define('DB_USER', 'your_username');       // Username database
define('DB_PASS', 'your_password');       // Password database
define('DB_CHARSET', 'utf8mb4');

// ===========================================
// CONFIGURAZIONE SICUREZZA
// ===========================================

// Session security
define('SESSION_TIMEOUT', 3600);          // Timeout sessione (secondi)
define('SESSION_REGENERATE_TIME', 300);   // Rigenera session ID ogni 5 min

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_STRONG_PASSWORD', true);

// ===========================================
// CONFIGURAZIONE EMAIL
// ===========================================

// Email settings (per notifiche ordini, conferme, etc.)
define('SMTP_HOST', 'your-smtp-host.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@domain.com');
define('SMTP_PASS', 'your-email-password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Tenuta Manarese');

// ===========================================
// CONFIGURAZIONE SITO
// ===========================================

// Site settings
define('SITE_NAME', 'Tenuta Manarese');
define('SITE_URL', 'https://your-domain.com');
define('ADMIN_EMAIL', 'admin@your-domain.com');

// Upload settings
define('UPLOAD_MAX_SIZE', 2097152);       // 2MB in bytes
define('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif');

// ===========================================
// CONFIGURAZIONE PAGAMENTI (se implementati)
// ===========================================

// Payment gateway settings (esempio PayPal/Stripe)
// define('PAYMENT_MODE', 'sandbox');     // 'sandbox' or 'live'
// define('PAYPAL_CLIENT_ID', 'your-paypal-client-id');
// define('PAYPAL_CLIENT_SECRET', 'your-paypal-secret');

// ===========================================
// FUNZIONI HELPER
// ===========================================

/**
 * Ottiene connessione al database
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Set timezone
            $pdo->exec("SET time_zone = '+01:00'");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    return $pdo;
}

/**
 * Configurazione sessioni sicure
 */
function configureSecureSessions() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurazione sicura sessioni
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        
        // Regenera session ID periodicamente
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > SESSION_REGENERATE_TIME) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Resto delle funzioni esistenti...
// (copia le altre funzioni dal tuo config.php attuale)

?>
