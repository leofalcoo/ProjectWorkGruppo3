<?php
require_once 'config.php';
require_once 'config_email.php';

$nome = $email = $telefono = $messaggio = "";
$error = "";
$success = false;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["nome"])) {
        $error = "Nome obbligatorio";
    } else {
        $nome = filter_var($_POST["nome"], FILTER_SANITIZE_STRING);
    }
    
    if (empty($_POST["email"])) {
        $error = "Email obbligatoria";
    } else {
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Formato email non valido";
        }
    }
    
    if (isset($_POST["telefono"])) {
        $telefono = filter_var($_POST["telefono"], FILTER_SANITIZE_STRING);
    }
    
    if (empty($_POST["messaggio"])) {
        $error = "Messaggio obbligatorio";
    } else {
        $messaggio = filter_var($_POST["messaggio"], FILTER_SANITIZE_STRING);
    }
    
    if (empty($error)) {
        try {
            $pdo = getDBConnection();
            
            if (!$pdo) {
                throw new Exception("Connessione al database non disponibile");
            }
            
            $stmt = $pdo->prepare("INSERT INTO contatti (nome, email, telefono, messaggio, data_invio) VALUES (?, ?, ?, ?, NOW())");
            
            if ($stmt->execute([$nome, $email, $telefono, $messaggio])) {
                // Messaggio salvato nel database, ora inviamo le email
                
                // 1. Email di notifica all'azienda
                $notification_sent = sendContactNotificationEmail($nome, $email, $telefono, $messaggio);
                
                // 2. Email di conferma al mittente
                $confirmation_sent = sendContactConfirmationEmail($nome, $email);
                
                $success = true;
                
                // Log dei risultati invio email (opzionale)
                if (!$notification_sent) {
                    error_log("CONTATTI: Errore invio email di notifica per: $email");
                }
                if (!$confirmation_sent) {
                    error_log("CONTATTI: Errore invio email di conferma per: $email");
                }
                
            } else {
                throw new Exception("Errore nell'invio del messaggio");
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messaggio Inviato - Tenuta Manarese</title>
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-white">
    <!-- Sezione di risposta -->
    <section class="py-5 bg-white" style="min-height: 70vh; display: flex; align-items: center;">
        <div class="container text-center">
            <?php if ($success): ?>
                <div class="feature-box p-5 mx-auto" style="max-width: 600px;">
                    <i class="fas fa-check-circle text-success fa-5x mb-4"></i>
                    <h2 class="mb-4 text-color-secondary">Messaggio Inviato con Successo</h2>
                    <p class="lead text-color-secondary">Grazie per averci contattato. Ti risponderemo al più presto.</p>
                    <div class="small text-muted mt-3">
                        <p>✉️ Ti abbiamo inviato una email di conferma</p>
                        <p>📞 Ti contatteremo entro 24-48 ore lavorative</p>
                    </div>
                    <div class="mt-4">
                        <a href="index.php" class="btn" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">Torna alla Home</a>
                    </div>
                    <p class="small text-muted mt-3">Sarai reindirizzato automaticamente tra 5 secondi.</p>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'contattaci.php?success=1';
                    }, 5000);
                </script>
            <?php else: ?>
                <div class="feature-box p-5 mx-auto" style="max-width: 600px;">
                    <i class="fas fa-exclamation-triangle text-danger fa-5x mb-4"></i>
                    <h2 class="mb-4">Si è verificato un errore</h2>
                    <p class="lead"><?php echo !empty($error) ? $error : "Errore durante l'invio del messaggio. Riprova più tardi."; ?></p>
                    <div class="mt-4">
                        <a href="contattaci.php" class="btn" style="background-color: var(--color-secondary); border-color: var(--color-secondary); color: white;">Torna al form</a>
                    </div>
                    <p class="small text-muted mt-3">Sarai reindirizzato automaticamente tra 5 secondi.</p>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'contattaci.php?error=1';
                    }, 5000);
                </script>
            <?php endif; ?>
        </div>
    </section> 
</body>
</html>