<?php
/**
 * File di controllo sessione automatico
 * Include questo file all'inizio di ogni pagina PHP per gestire il login automatico
 */

// Includi il file di configurazione
require_once __DIR__ . '/config.php';

// Avvia la sessione se non è già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controlla se l'utente non è loggato ma ha un cookie "ricordami"
if (!isset($_SESSION['logged_in']) && isset($_COOKIE['remember_token'])) {
    try {
        $pdo = getDBConnection();
        
        // Verifica il token remember
        $stmt = $pdo->prepare("SELECT id, nome, cognome, email FROM utenti WHERE remember_token = ? AND remember_expires > NOW() AND attivo = 1");
        $stmt->execute([$_COOKIE['remember_token']]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utente) {
            // Login automatico
            $_SESSION['user_id'] = $utente['id'];
            $_SESSION['user_nome'] = $utente['nome'];
            $_SESSION['user_cognome'] = $utente['cognome'];
            $_SESSION['user_email'] = $utente['email'];
            $_SESSION['logged_in'] = true;
            
            // Aggiorna ultimo accesso
            $stmt = $pdo->prepare("UPDATE utenti SET ultimo_accesso = NOW() WHERE id = ?");
            $stmt->execute([$utente['id']]);
        } else {
            // Token non valido o scaduto, rimuovi il cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    } catch (PDOException $e) {
        // Errore database, ignora silenziosamente
    }
}

// Variabili globali per uso nelle pagine
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_nome = $is_logged_in ? $_SESSION['user_nome'] : '';
$user_cognome = $is_logged_in ? $_SESSION['user_cognome'] : '';
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
?>
