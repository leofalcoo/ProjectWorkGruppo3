<?php
require_once 'config.php';
require_once 'config_email.php';

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    
    // Validazione input
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['success'] = false;
        $response['message'] = "Inserisci un indirizzo email valido";
        echo json_encode($response);
        exit;
    }
    
    try {
        // Connessione al database
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception("Connessione database non disponibile");
        }
        
        // Verifica se l'email esiste
        $stmt = $pdo->prepare("SELECT id, nome, cognome FROM utenti WHERE email = ? AND attivo = 1");
        $stmt->execute([$email]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utente) {
            // Genera token per reset password
            $reset_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 ora
            
            // Salva il token nel database
            $stmt = $pdo->prepare("UPDATE utenti SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?");
            $stmt->execute([$reset_token, $expires, $email]);
            
            // Crea link di reset
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $reset_token;
            
            // In un ambiente reale, qui invieresti l'email
            // Contenuto dell'email di reset
            $subject = "Reset Password - Tenuta Manarese";
            $message = "
            Ciao " . $utente['nome'] . ",
            
            Hai richiesto il reset della tua password per l'account Tenuta Manarese.
            
            Clicca sul seguente link per reimpostare la tua password:
            " . $reset_link . "
            
            Questo link è valido per 1 ora.
            
            Se non hai richiesto tu il reset, ignora questa email.
            
            Saluti,
            Team Tenuta Manarese
            ";
            
            // Invio email di reset password
            $email_sent = sendPasswordResetEmail($email, $utente['nome'], $utente['cognome'], $reset_token);
            
            $response['success'] = true;
            if ($email_sent) {
                $response['message'] = "Ti abbiamo inviato un'email con le istruzioni per recuperare la password";
            } else {
                $response['message'] = "Reset password generato, ma c'è stato un problema nell'invio dell'email. Controlla i log del server.";
            }
            
        } else {
            // Per sicurezza, non rivelare se l'email esiste o meno
            $response['success'] = true;
            $response['message'] = "Se l'email esiste nel nostro sistema, riceverai le istruzioni per il recupero";
        }
        
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Errore del sistema. Riprova più tardi.";
    }
    
} else {
    $response['success'] = false;
    $response['message'] = "Metodo non consentito";
}

// Restituisci sempre JSON per questa richiesta
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
