<?php
require_once 'config.php';

// Configura sessione sicura
configureSecureSessions();

// Ottieni connessione database dal config
$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Errore di connessione al database']);
    exit;
}

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $remember = isset($_POST["remember"]);
    $ip = $_SERVER['REMOTE_ADDR']; // Rileva IP per il logging
    $userAgent = $_SERVER['HTTP_USER_AGENT']; // Rileva user agent per il logging
    
    // Validazione input
    if (empty($email) || empty($password)) {
        $response['success'] = false;
        $response['message'] = "Email e password sono obbligatori";
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['success'] = false;
        $response['message'] = "Formato email non valido";
        echo json_encode($response);
        exit;
    }
    
    try {
        // Cerca l'utente nel database (aggiungi i campi per la sicurezza e il ruolo)
        $stmt = $pdo->prepare("SELECT id, nome, cognome, email, password, attivo, ruolo, failed_login_attempts, account_locked_until FROM utenti WHERE email = ?");
        $stmt->execute([$email]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Controlla se l'account è bloccato (nuova verifica)
        if ($utente && isAccountLocked($pdo, $utente['id'])) {
            $response['success'] = false;
            $response['message'] = "Account temporaneamente bloccato. Riprova più tardi.";
            echo json_encode($response);
            exit;
        }
        
        if ($utente && password_verify($password, $utente['password'])) {
            // Controlla se l'account è attivo
            if ($utente['attivo'] == 0) {
                $response['success'] = false;
                $response['message'] = "Account non ancora attivato. Controlla la tua email per il link di attivazione.";
                echo json_encode($response);
                exit;
            }
            
            // Login riuscito - resetta i tentativi falliti e registra l'accesso
            resetFailedAttempts($pdo, $utente['id']);
            logLoginAttempt($pdo, $utente['id'], $ip, $userAgent, true);
            
            // Imposta la sessione sicura
            secureLogin($utente['id'], $utente);
            
            // Aggiorna ultimo accesso
            $stmt = $pdo->prepare("UPDATE utenti SET ultimo_accesso = NOW() WHERE id = ?");
            $stmt->execute([$utente['id']]);
            
            // Se "Ricordami" è selezionato, crea un cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (30 * 24 * 60 * 60); // 30 giorni
                
                // Salva il token nel database
                $stmt = $pdo->prepare("UPDATE utenti SET remember_token = ?, remember_expires = FROM_UNIXTIME(?) WHERE id = ?");
                $stmt->execute([$token, $expires, $utente['id']]);
                
                // Imposta il cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }
            
            $response['success'] = true;
            $response['message'] = "Login effettuato con successo!";
            $response['redirect'] = "index.php";
            
            // Debug: assicuriamoci che la risposta sia corretta
            error_log("Login success for user: " . $utente['email']);
            
        } else {
            // Login fallito - registra il tentativo e gestisci i fallimenti
            if ($utente) {
                logLoginAttempt($pdo, $utente['id'], $ip, $userAgent, false);
                handleFailedLogin($pdo, $utente['id']);
            } else {
                // Registra tentativi anche per email non esistenti
                logLoginAttempt($pdo, null, $ip, $userAgent, false);
            }
            
            $response['success'] = false;
            $response['message'] = "Email o password non corretti";
        }
        
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Errore del database: " . $e->getMessage();
    }
    
} else {
    $response['success'] = false;
    $response['message'] = "Metodo non consentito";
}

// Output
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    if ($response['success']) {
        header("Location: index.php?login=success");
    } else {
        header("Location: login.php?error=" . urlencode($response['message']));
    }
}
exit;
?>