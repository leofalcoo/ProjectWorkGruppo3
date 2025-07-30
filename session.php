<?php
// session.php - Gestione sessioni e autenticazione
session_start();

// Funzione per verificare se l'utente è loggato
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

// Funzione per verificare se l'utente è un admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_ruolo']) && $_SESSION['user_ruolo'] === 'admin';
}

// Funzione per ottenere l'ID dell'utente loggato
function getUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

// Funzione per ottenere il ruolo dell'utente loggato
function getUserRole() {
    return isLoggedIn() ? ($_SESSION['user_ruolo'] ?? 'cliente') : null;
}

// Funzione per ottenere i dati dell'utente loggato
function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'nome' => $_SESSION['user_nome'] ?? '',
        'cognome' => $_SESSION['user_cognome'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'ruolo' => $_SESSION['user_ruolo'] ?? 'cliente'
    ];
}

// Funzione per reindirizzare al login se non loggato
function requireLogin($redirect_url = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect_url?error=login_required");
        exit;
    }
}

// Funzione per richiedere privilegi admin
function requireAdmin($redirect_url = 'index.php') {
    requireLogin();
    if (!isAdmin()) {
        header("Location: $redirect_url?error=access_denied");
        exit;
    }
}
?>
