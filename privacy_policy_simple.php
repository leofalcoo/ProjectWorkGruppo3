<?php
// privacy_policy_simple.php - Privacy Policy semplificata
require_once 'config.php';
configureSecureSessions();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Tenuta Manarese</title>
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-leaf me-2"></i>Tenuta Manarese
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link" href="profilo.php">Profilo</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.html">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h1 class="h3 mb-0">Privacy Policy</h1>
                    </div>
                    <div class="card-body">
                        
                        <h4>1. Chi siamo</h4>
                        <p><strong>Tenuta Manarese</strong><br>
                        Email: info@tenutamanarese.it<br>
                        Telefono: 0542 684057</p>

                        <h4>2. Dati che raccogliamo</h4>
                        <ul>
                            <li><strong>Registrazione:</strong> Nome, cognome, email, password</li>
                            <li><strong>Navigazione:</strong> IP, browser, pagine visitate</li>
                            <li><strong>Cookie:</strong> Sessione, preferenze, sicurezza</li>
                        </ul>

                        <h4>3. Perché li usiamo</h4>
                        <ul>
                            <li>Gestire il tuo account e i tuoi ordini</li>
                            <li>Garantire la sicurezza del sito</li>
                            <li>Migliorare i nostri servizi</li>
                            <li>Comunicazioni importanti</li>
                        </ul>

                        <h4>4. I tuoi diritti</h4>
                        <p>Secondo il GDPR, puoi:</p>
                        <ul>
                            <li><strong>Accedere</strong> ai tuoi dati</li>
                            <li><strong>Correggere</strong> informazioni sbagliate</li>
                            <li><strong>Cancellare</strong> il tuo account</li>
                            <li><strong>Limitare</strong> l'uso dei dati</li>
                            <li><strong>Esportare</strong> i tuoi dati</li>
                        </ul>

                        <?php if (isLoggedIn()): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-user-shield me-2"></i>
                            <strong>Sei loggato:</strong> 
                            <a href="gdpr_simple.php" class="alert-link">Gestisci i tuoi diritti GDPR</a>
                        </div>
                        <?php endif; ?>

                        <h4>5. Cookie</h4>
                        <p>Usiamo cookie per:</p>
                        <ul>
                            <li><strong>Necessari:</strong> Login, sicurezza (sempre attivi)</li>
                            <li><strong>Funzionali:</strong> Preferenze, carrello</li>
                        </ul>

                        <h4>6. Sicurezza</h4>
                        <p>Proteggiamo i tuoi dati con:</p>
                        <ul>
                            <li>Password crittografate</li>
                            <li>Connessioni sicure HTTPS</li>
                            <li>Accessi monitorati</li>
                        </ul>

                        <h4>7. Contatti</h4>
                        <p>Per domande sulla privacy: <strong>info@tenutamanarese.it</strong></p>

                        <hr>
                        <p class="text-muted small">
                            Aggiornato: <?php echo date('d/m/Y'); ?> - Conforme GDPR
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/cookie_manager.js"></script>
</body>
</html>
