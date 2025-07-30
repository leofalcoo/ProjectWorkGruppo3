<?php
// privacy_policy.php - Informativa Privacy completa GDPR
require_once 'config.php';
configureSecureSessions();

$pageTitle = "Informativa Privacy - Tenuta Manarese";
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-leaf me-2"></i>Tenuta Manarese
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prodotti.html">Prodotti</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contattaci.html">Contattaci</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="carrello.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="badge bg-warning text-dark" id="cart-counter">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profilo.php">Profilo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.html">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Informativa Privacy
                        </h1>
                    </div>
                    <div class="card-body">
                        
                        <!-- Sezione 1: Identità del Titolare -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">1. Titolare del Trattamento</h2>
                            <div class="alert alert-light">
                                <strong>Tenuta Manarese</strong><br>
                                Email: info@tenutamanarese.it<br>
                                Telefono: +39 123 456 7890<br>
                                Indirizzo: Via dei Vigneti, 123 - 12345 Località Vitivinicola (CN)
                            </div>
                        </section>

                        <!-- Sezione 2: Dati Raccolti -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">2. Dati Personali Raccolti</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Dati di Registrazione:</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Nome e Cognome</li>
                                        <li class="list-group-item">Indirizzo Email</li>
                                        <li class="list-group-item">Password (crittografata)</li>
                                        <li class="list-group-item">Data di registrazione</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5>Dati di Navigazione:</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Indirizzo IP</li>
                                        <li class="list-group-item">User Agent del browser</li>
                                        <li class="list-group-item">Cookie di sessione</li>
                                        <li class="list-group-item">Pagine visitate</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <!-- Sezione 3: Finalità del Trattamento -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">3. Finalità del Trattamento</h2>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Finalità</th>
                                            <th>Base Giuridica</th>
                                            <th>Dati Utilizzati</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Registrazione e autenticazione utenti</td>
                                            <td>Consenso (Art. 6.1.a GDPR)</td>
                                            <td>Nome, email, password</td>
                                        </tr>
                                        <tr>
                                            <td>Gestione carrello e ordini</td>
                                            <td>Esecuzione contratto (Art. 6.1.b GDPR)</td>
                                            <td>Dati di registrazione, preferenze</td>
                                        </tr>
                                        <tr>
                                            <td>Comunicazioni di servizio</td>
                                            <td>Interesse legittimo (Art. 6.1.f GDPR)</td>
                                            <td>Email, preferenze comunicazione</td>
                                        </tr>
                                        <tr>
                                            <td>Sicurezza e prevenzione frodi</td>
                                            <td>Interesse legittimo (Art. 6.1.f GDPR)</td>
                                            <td>IP, User Agent, log di accesso</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Sezione 4: Conservazione Dati -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">4. Conservazione dei Dati</h2>
                            <div class="alert alert-info">
                                <h6>Tempi di Conservazione:</h6>
                                <ul class="mb-0">
                                    <li><strong>Dati di registrazione:</strong> Fino alla cancellazione dell'account</li>
                                    <li><strong>Dati di navigazione:</strong> 12 mesi dall'ultima visita</li>
                                    <li><strong>Log di sicurezza:</strong> 24 mesi per finalità di sicurezza</li>
                                    <li><strong>Cookie tecnici:</strong> Durata della sessione</li>
                                </ul>
                            </div>
                        </section>

                        <!-- Sezione 5: Diritti dell'Interessato -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">5. I Tuoi Diritti (Art. 15-22 GDPR)</h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">Diritti di Accesso e Controllo</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>Accesso ai dati</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Rettifica dati errati</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Cancellazione dati</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Limitazione trattamento</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">Diritti di Portabilità</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>Portabilità dati</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Opposizione al trattamento</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Revoca consenso</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Reclamo all'Autorità</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-warning">
                                    <strong>Come esercitare i tuoi diritti:</strong><br>
                                    Invia una richiesta a <strong>info@tenutamanarese.it</strong> specificando il diritto che intendi esercitare. 
                                    Risponderemo entro 30 giorni dalla ricezione della richiesta.
                                </div>
                            </div>
                        </section>

                        <!-- Sezione 6: Cookie -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">6. Utilizzo dei Cookie</h2>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Tipo Cookie</th>
                                            <th>Nome</th>
                                            <th>Finalità</th>
                                            <th>Durata</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-primary">Tecnico</span></td>
                                            <td>PHPSESSID</td>
                                            <td>Gestione sessione utente</td>
                                            <td>Sessione</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning">Preferenze</span></td>
                                            <td>cookie_consent</td>
                                            <td>Memorizza preferenze cookie</td>
                                            <td>12 mesi</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info">Sicurezza</span></td>
                                            <td>csrf_token</td>
                                            <td>Protezione CSRF</td>
                                            <td>Sessione</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Sezione 7: Sicurezza -->
                        <section class="mb-5">
                            <h2 class="h4 text-success mb-3">7. Misure di Sicurezza</h2>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-lock fa-3x text-success mb-2"></i>
                                        <h6>Crittografia</h6>
                                        <p class="small">Password hashate con algoritmi sicuri</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-shield-alt fa-3x text-success mb-2"></i>
                                        <h6>Sessioni Sicure</h6>
                                        <p class="small">Cookie HttpOnly e Secure</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-eye fa-3x text-success mb-2"></i>
                                        <h6>Monitoraggio</h6>
                                        <p class="small">Log di accesso e attività</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Sezione 8: Contatti -->
                        <section class="mb-4">
                            <h2 class="h4 text-success mb-3">8. Contatti per Privacy</h2>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-2">Per qualsiasi domanda relativa al trattamento dei tuoi dati personali:</p>
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-envelope text-success me-2"></i><strong>Email:</strong> privacy@tenutamanarese.it</li>
                                        <li><i class="fas fa-phone text-success me-2"></i><strong>Telefono:</strong> +39 123 456 7890</li>
                                        <li><i class="fas fa-clock text-success me-2"></i><strong>Orari:</strong> Lunedì - Venerdì, 9:00 - 18:00</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <!-- Footer informativo -->
                        <div class="text-center mt-4 pt-4 border-top">
                            <p class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Ultima modifica: <?php echo date('d/m/Y'); ?> - 
                                Conforme al Regolamento UE 2016/679 (GDPR)
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    
    <script>
        // Aggiorna il contatore del carrello se l'utente è loggato
        <?php if (isLoggedIn()): ?>
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCounter();
        });
        <?php endif; ?>
    </script>
</body>
</html>
