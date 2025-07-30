<?php
// Configurazione di base
$page_title = "Login - Tenuta Manarese";
$current_page = "login";

// Include gestione sessioni e configurazione
session_start();
require_once 'config.php';

// Gestione messaggi di successo/errore
$message = "";
$message_type = "";

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'registered') {
        $message = "Registrazione completata! Controlla la tua email per attivare l'account.";
        $message_type = "success";
    } elseif ($_GET['success'] == 'activated') {
        $message = "Account attivato con successo! Ora puoi effettuare l'accesso.";
        $message_type = "success";
    }
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] == 'login_failed') {
        $message = "Email o password non corretti.";
        $message_type = "danger";
    } elseif ($_GET['error'] == 'registration_failed') {
        $message = "Errore durante la registrazione. Riprova più tardi.";
        $message_type = "danger";
    } elseif ($_GET['error'] == 'login_required') {
        $message = "Devi effettuare l'accesso per accedere a questa pagina.";
        $message_type = "warning";
    }
}

$user_logged_in = isLoggedIn();
$carrello_count = $user_logged_in ? getCarrelloCount(getUserId()) : 0;

// Se l'utente è già loggato, reindirizza alla homepage
if ($user_logged_in) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/login.js"></script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand text-color-secondary" href="index.php">Tenuta Manarese</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'home' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'prodotti' ? 'active' : ''; ?>" href="prodotti.php">Prodotti</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'contattaci' ? 'active' : ''; ?>" href="contattaci.php">Contattaci</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'login' ? 'active' : ''; ?>" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="hero" style="background-image: url('immagini/vite.jpg'); padding: 4rem 0;">
        <div class="container text-center hero-content">
            <h1 class="display-4 fw-bold mb-4 text-color-secondary">Area Clienti</h1>
            <p class="lead fs-5">Accedi al tuo account o registrati per iniziare</p>
        </div>
    </header>

    <!-- Sezione Login/Registrazione -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tabs per Login/Registrazione -->
                    <ul class="nav nav-tabs nav-fill mb-4" id="loginRegisterTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active login-tab" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab">
                                <i class="fas fa-sign-in-alt me-2"></i>Accedi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link login-tab" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab">
                                <i class="fas fa-user-plus me-2"></i>Registrati
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="loginRegisterContent">
                        <!-- Tab Login -->
                        <div class="tab-pane fade show active" id="login-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-4 mb-md-0">
                                    <div class="login-form">
                                        <h2 class="mb-4">Accedi al tuo account</h2>
                                        
                                        <!-- Messaggio di errore/successo -->
                                        <div id="login-message" class="alert d-none" role="alert"></div>
                                        
                                        <form id="loginForm" action="process_login.php" method="POST">
                                            <div class="mb-3">
                                                <label for="login-email" class="form-label">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                    <input type="email" class="form-control" id="login-email" name="email" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="login-password" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                    <input type="password" class="form-control" id="login-password" name="password" required>
                                                    <button class="btn btn-outline-secondary" type="button" id="toggleLoginPassword">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
                                                <label class="form-check-label" for="remember-me">Ricordami</label>
                                            </div>
                                            <button type="submit" class="btn w-100 mb-3" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                                <i class="fas fa-sign-in-alt me-2"></i>Accedi
                                            </button>
                                            <div class="text-center">
                                                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                                    Password dimenticata?
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="login-info">
                                        <h3>Perché registrarsi?</h3>
                                        <div class="divider"></div>
                                        <ul class="list-unstyled">
                                            <li class="mb-3">
                                                <i class="fas fa-shopping-cart text-color-secondary me-2"></i>
                                                <strong>Carrello personale</strong><br>
                                                <small class="text-muted">Salva i tuoi prodotti preferiti e acquista quando vuoi</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-history text-color-secondary me-2"></i>
                                                <strong>Storico ordini</strong><br>
                                                <small class="text-muted">Tieni traccia di tutti i tuoi acquisti</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-bell text-color-secondary me-2"></i>
                                                <strong>Notifiche personalizzate</strong><br>
                                                <small class="text-muted">Ricevi aggiornamenti sui nuovi prodotti e offerte speciali</small>
                                            </li>
                                            <li class="mb-3">
                                                <i class="fas fa-truck text-color-secondary me-2"></i>
                                                <strong>Spedizioni rapide</strong><br>
                                                <small class="text-muted">Indirizzi salvati per ordini più veloci</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Registrazione -->
                        <div class="tab-pane fade" id="register-pane" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="login-form">
                                        <h2 class="mb-4 text-center">Crea il tuo account</h2>
                                        
                                        <!-- Messaggio di errore/successo -->
                                        <div id="register-message" class="alert d-none" role="alert"></div>
                                        
                                        <form id="registerForm" action="process_register.php" method="POST">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="register-nome" class="form-label">Nome</label>
                                                    <input type="text" class="form-control" id="register-nome" name="nome" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="register-cognome" class="form-label">Cognome</label>
                                                    <input type="text" class="form-control" id="register-cognome" name="cognome" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="register-email" class="form-label">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                    <input type="email" class="form-control" id="register-email" name="email" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="register-telefono" class="form-label">Telefono (opzionale)</label>
                                                <input type="tel" class="form-control" id="register-telefono" name="telefono">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="register-password" class="form-label">Password</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                        <input type="password" class="form-control" id="register-password" name="password" required>
                                                        <button class="btn btn-outline-secondary" type="button" id="toggleRegisterPassword">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="register-confirm-password" class="form-label">Conferma Password</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                        <input type="password" class="form-control" id="register-confirm-password" name="confirm_password" required>
                                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="register-indirizzo" class="form-label">Indirizzo (opzionale)</label>
                                                <input type="text" class="form-control" id="register-indirizzo" name="indirizzo" placeholder="Via, numero civico">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="register-cap" class="form-label">CAP</label>
                                                    <input type="text" class="form-control" id="register-cap" name="cap" maxlength="5">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="register-citta" class="form-label">Città</label>
                                                    <input type="text" class="form-control" id="register-citta" name="citta">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label for="register-provincia" class="form-label">Provincia</label>
                                                    <input type="text" class="form-control" id="register-provincia" name="provincia" maxlength="2" placeholder="BO">
                                                </div>
                                            </div>
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="register-privacy" name="privacy" required>
                                                <label class="form-check-label small" for="register-privacy">
                                                    Acconsento al trattamento dei dati personali ai sensi del GDPR
                                                </label>
                                            </div>
                                            <div class="mb-4 form-check">
                                                <input type="checkbox" class="form-check-input" id="register-newsletter" name="newsletter">
                                                <label class="form-check-label small" for="register-newsletter">
                                                    Desidero ricevere la newsletter con offerte e novità
                                                </label>
                                            </div>
                                            <button type="submit" class="btn w-100" style="background-color: var(--color-secondary); border-color: var(--color-secondary); color: white;">
                                                <i class="fas fa-user-plus me-2"></i>Registrati
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Password Dimenticata -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recupera Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Inserisci la tua email per ricevere il link di recupero password.</p>
                    <form id="forgotPasswordForm" action="forgot_password.php" method="POST">
                        <div class="mb-3">
                            <label for="forgot-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="forgot-email" name="email" required>
                        </div>
                        <button type="submit" class="btn w-100" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">Invia link di recupero</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="mb-3 text-color-secondary">Tenuta Manarese</h4>
                    <p>Passione per la terra, amore per la qualità dal 1960.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h4 class="mb-3 text-color-secondary">Contatti</h4>
                    <p>Tel: 0542 684057<br>Email: info@tenutamanarese.it</p>
                </div>
                <div class="col-md-4">
                    <h4 class="mb-3 text-color-secondary">Orari</h4>
                    <p>Lun-Ven: 9:00-11:30, 16:00-18:30<br>Sabato: 9:00-11:30</p>
                </div>
            </div>
            <div class="mt-4">
                <p class="mb-0 text-color-secondary">&copy; <?php echo date('Y'); ?> Tenuta Manarese. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript per gestire la visualizzazione delle password
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility per login
            document.getElementById('toggleLoginPassword').addEventListener('click', function() {
                togglePasswordVisibility('login-password', this);
            });

            // Toggle password visibility per registrazione
            document.getElementById('toggleRegisterPassword').addEventListener('click', function() {
                togglePasswordVisibility('register-password', this);
            });

            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                togglePasswordVisibility('register-confirm-password', this);
            });

            function togglePasswordVisibility(inputId, button) {
                const input = document.getElementById(inputId);
                const icon = button.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }

            // Validazione password match in tempo reale
            const password = document.getElementById('register-password');
            const confirmPassword = document.getElementById('register-confirm-password');
            
            function validatePasswordMatch() {
                if (confirmPassword.value !== password.value) {
                    confirmPassword.setCustomValidity('Le password non corrispondono');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('input', validatePasswordMatch);
            confirmPassword.addEventListener('input', validatePasswordMatch);
        });
    </script>
</body>

</html>
