<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config_email.php';

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Raccolta e sanificazione dati
    $nome = trim($_POST["nome"]);
    $cognome = trim($_POST["cognome"]);
    $email = trim($_POST["email"]);
    $telefono = trim($_POST["telefono"] ?? "");
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $indirizzo = trim($_POST["indirizzo"] ?? "");
    $cap = trim($_POST["cap"] ?? "");
    $citta = trim($_POST["citta"] ?? "");
    $provincia = trim($_POST["provincia"] ?? "");
    $newsletter = isset($_POST["newsletter"]) ? 1 : 0;
    
    // Validazione input
    $errors = array();
    
    if (empty($nome) || strlen($nome) < 2) {
        $errors[] = "Il nome deve essere di almeno 2 caratteri";
    }
    
    if (empty($cognome) || strlen($cognome) < 2) {
        $errors[] = "Il cognome deve essere di almeno 2 caratteri";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Inserisci un indirizzo email valido";
    }
    
    if (!empty($telefono) && !preg_match('/^[0-9+\s()-]{6,20}$/', $telefono)) {
        $errors[] = "Formato telefono non valido";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "La password deve essere di almeno 6 caratteri";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Le password non corrispondono";
    }
    
    // Validazione password più robusta
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = "La password deve contenere almeno una lettera maiuscola, una minuscola e un numero";
    }
    
    if (!empty($cap) && !preg_match('/^\d{5}$/', $cap)) {
        $errors[] = "Il CAP deve essere di 5 cifre";
    }
    
    if (!empty($provincia) && !preg_match('/^[A-Z]{2}$/', strtoupper($provincia))) {
        $errors[] = "La provincia deve essere di 2 lettere (es. BO)";
    }
    
    if (!isset($_POST["privacy"])) {
        $errors[] = "Devi accettare la privacy policy";
    }
    
    if (!empty($errors)) {
        $response['success'] = false;
        $response['message'] = implode(", ", $errors);
        echo json_encode($response);
        exit;
    }
    
    try {
        // Connessione al database
        $pdo = getDbConnection();
        
        // Controlla se l'email esiste già
        $stmt = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $response['success'] = false;
            $response['message'] = "Questa email è già registrata";
            echo json_encode($response);
            exit;
        }
        
        // Hash della password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Genera token di attivazione
        $activation_token = bin2hex(random_bytes(32));
        
        // Inserimento nel database
        $sql = "INSERT INTO utenti (nome, cognome, email, telefono, password, indirizzo, cap, citta, provincia, newsletter, activation_token, attivo, data_registrazione) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $nome, 
            $cognome, 
            $email, 
            $telefono, 
            $hashed_password, 
            $indirizzo, 
            $cap, 
            $citta, 
            strtoupper($provincia), 
            $newsletter, 
            $activation_token
        ]);
        
        if ($result) {
            $user_id = $pdo->lastInsertId();
            
            // Invio email di attivazione usando la funzione dedicata
            $email_sent = sendActivationEmail($email, $nome, $cognome, $activation_token);
            
            if ($email_sent) {
                $response['success'] = true;
                $response['message'] = "Registrazione completata con successo! 🎉\n\nTi abbiamo inviato un'email di attivazione all'indirizzo: " . $email . "\n\n📧 Controlla la tua casella di posta (e anche la cartella spam) per attivare il tuo account.";
                $response['redirect'] = "login.php?registered=true";
            } else {
                // Se l'invio email fallisce, salviamo comunque l'utente ma informiamo del problema
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $path = dirname($_SERVER['PHP_SELF']);
                $activation_link = "$protocol://$host$path/activate.php?token=$activation_token";
                
                $response['success'] = true;
                $response['message'] = "Registrazione completata! ⚠️ Problema nell'invio email.\n\nPuoi attivare il tuo account cliccando direttamente su questo link:\n" . $activation_link . "\n\n(Salva questo link per attivare il tuo account)";
                $response['redirect'] = "login.php?registered=true";
            }
            
        } else {
            $response['success'] = false;
            $response['message'] = "Errore durante la registrazione";
        }
        
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Errore del database: " . $e->getMessage();
    }
    
} else {
    $response['success'] = false;
    $response['message'] = "Metodo non consentito";
}

// Se la richiesta è AJAX, restituisci JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Altrimenti reindirizza
    if ($response['success']) {
        header("Location: login.php?registered=true");
    } else {
        header("Location: login.php?register_error=" . urlencode($response['message']));
    }
}
exit;
?>
