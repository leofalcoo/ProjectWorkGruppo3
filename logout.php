<?php
require_once 'config.php';

// Configura sessione sicura
configureSecureSessions();

// Rimuovi il cookie "ricordami" se esiste
if (isset($_COOKIE['remember_token'])) {
    // Rimuovi il token dal database 
    
    try {
        $pdo = getDBConnection();
        
        // Rimuovi il token dal database
        if (isset($_SESSION['user_id']) && $pdo) {
            $stmt = $pdo->prepare("UPDATE utenti SET remember_token = NULL, remember_expires = NULL WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
    } catch (PDOException $e) {
        // Log dell'errore se necessario
    }
    
    // Rimuovi il cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Logout sicuro
secureLogout();

// Reindirizza alla homepage
header("Location: index.php?logout=true");
exit;
?>
