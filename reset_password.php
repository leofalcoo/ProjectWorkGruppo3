<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config_email.php';

$message = "";
$success = false;
$show_form = false;
$token = "";

// Gestione GET (visualizzazione form)
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Connessione al database
        $pdo = getDbConnection();
        
        // Verifica token e scadenza
        $stmt = $pdo->prepare("SELECT id, nome, email FROM utenti WHERE password_reset_token = ? AND password_reset_expires > NOW() AND attivo = 1");
        $stmt->execute([$token]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utente) {
            $show_form = true;
        } else {
            $message = "Il link di reset è scaduto o non valido. Richiedi un nuovo reset password.";
        }
        
    } catch (PDOException $e) {
        $message = "Errore del database. Riprova più tardi.";
    }
}

// Gestione POST (elaborazione reset)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validazione password
    $errors = array();
    
    if (empty($new_password) || strlen($new_password) < 6) {
        $errors[] = "La password deve essere di almeno 6 caratteri";
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        $errors[] = "La password deve contenere almeno una lettera maiuscola, una minuscola e un numero";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Le password non corrispondono";
    }
    
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            
            // Verifica token ancora valido
            $stmt = $pdo->prepare("SELECT id, nome, email FROM utenti WHERE password_reset_token = ? AND password_reset_expires > NOW() AND attivo = 1");
            $stmt->execute([$token]);
            $utente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($utente) {
                // Aggiorna password e rimuovi token
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utenti SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?");
                $stmt->execute([$hashed_password, $utente['id']]);
                
                $success = true;
                $message = "Password reimpostata con successo! Ora puoi effettuare il login con la nuova password.";
            } else {
                $message = "Il link di reset è scaduto o non valido.";
            }
            
        } catch (PDOException $e) {
            $message = "Errore del database. Riprova più tardi.";
        }
    } else {
        $message = implode(", ", $errors);
        $show_form = true;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Tenuta Manarese</title>
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reset-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #228B22 0%, #32CD32 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-header i {
            font-size: 4rem;
            color: #228B22;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #228B22;
            border-color: #228B22;
            padding: 12px 30px;
        }
        .btn-primary:hover {
            background-color: #32CD32;
            border-color: #32CD32;
        }
        .alert {
            border-radius: 10px;
            padding: 20px;
        }
        .success-icon {
            color: #28a745;
        }
        .error-icon {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <?php if ($success): ?>
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="text-success">Password Reimpostata!</h2>
                <?php elseif ($show_form): ?>
                    <i class="fas fa-key"></i>
                    <h2 class="text-color-primary">Reimposta Password</h2>
                    <p class="text-muted">Inserisci la tua nuova password</p>
                <?php else: ?>
                    <i class="fas fa-times-circle error-icon"></i>
                    <h2 class="text-danger">Link Non Valido</h2>
                <?php endif; ?>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> text-center">
                    <i class="fas <?= $success ? 'fa-check' : 'fa-exclamation-triangle' ?> me-2"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($show_form): ?>
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Nuova Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               minlength="6" placeholder="Inserisci la nuova password">
                        <div class="form-text">
                            Deve contenere almeno 6 caratteri, una maiuscola, una minuscola e un numero
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Conferma Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                               placeholder="Conferma la nuova password">
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Reimposta Password
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Torna al Login
                </a>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="fas fa-home me-1"></i>
                    <a href="index.php" class="text-decoration-none">Torna alla Home - Tenuta Manarese</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validazione real-time delle password
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Le password non corrispondono');
            } else {
                this.setCustomValidity('');
            }
        });

        // Mostra/nascondi password
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
