<?php
// Configurazione di base
$page_title = "Contattaci - Tenuta Manarese";
$current_page = "contattaci";

// Avvia la sessione
session_start();

// Include configurazione principale
require_once 'config.php';

// Gestione messaggi di successo/errore
$message = "";
$message_type = "";

if (isset($_GET['success'])) {
    $message = "Messaggio inviato con successo! Ti risponderemo al più presto.";
    $message_type = "success";
} elseif (isset($_GET['error'])) {
    $message = "Errore nell'invio del messaggio. Riprova più tardi.";
    $message_type = "danger";
}

$user_logged_in = isLoggedIn();
$carrello_count = $user_logged_in ? getCartCount() : 0;
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
                    <?php if ($user_logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="carrello_view.php">
                                <i class="fas fa-shopping-cart"></i> Carrello
                                <?php if ($carrello_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $carrello_count; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item"><a class="nav-link" href="gestione.php">Gestione</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="profilo.php">Profilo</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?php echo $current_page == 'login' ? 'active' : ''; ?>" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="hero" style="background-image: url('immagini/autunno.jpg'); padding: 6rem 0;">
        <div class="container text-center hero-content">
            <h1 class="display-4 fw-bold mb-4 text-color-secondary">Contattaci</h1>
            <p class="lead fs-5">Siamo qui per rispondere alle tue domande</p>
        </div>
    </header>

    <!-- Sezione contatti -->
    <section class="py-5 bg-white">
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Colonna sinistra - Informazioni di contatto -->
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <h2 class="mb-4">I Nostri Contatti</h2>
                    
                    <div class="feature-box">
                        <div class="mb-4">
                            <h3 class="h5"><i class="fas fa-map-marker-alt me-2"></i> Dove siamo</h3>
                            <p class="mb-0">Via della Tenuta, 123<br>40026 Imola (BO)</p>
                        </div>
                    </div>
                    
                    <div class="feature-box">
                        <div class="mb-4">
                            <h3 class="h5"><i class="fas fa-phone me-2"></i> Telefono</h3>
                            <p class="mb-1">Fisso: <a href="tel:0542684057">0542 684057</a></p>
                            <p class="mb-0">Mobile: <a href="tel:3661430025">366 143 0025</a></p>
                        </div>
                    </div>

                    <div class="feature-box">
                        <div class="mb-4">
                            <h3 class="h5"><i class="fas fa-envelope me-2"></i> Email</h3>
                            <p class="mb-0"><a href="mailto:info@tenutamanarese.it">info@tenutamanarese.it</a></p>
                        </div>
                    </div>

                    <div class="feature-box">
                        <div>
                            <h3 class="h5"><i class="fas fa-clock me-2"></i> Orari estivi (Dal 1/06 al 1/09)</h3>
                            <ul class="list-unstyled mb-2">
                                <li><strong>Lunedì-Venerdì:</strong> 9:00-11:30, 16:00-18:30</li>
                                <li><strong>Sabato:</strong> 9:00-11:30</li>
                                <li><strong>Domenica:</strong> Chiuso</li>
                            </ul>
                            <p class="small text-muted mb-0">Per visite in orari differenti si prega di chiamare e chiedere disponibilità</p>
                        </div>
                    </div>

                    <div class="feature-box">
                        <div>
                            <h3 class="h5"><i class="fas fa-user me-2"></i> Referente</h3>
                            <p class="mb-0">Catia Bertuzzi</p>
                        </div>
                    </div>
                </div>

                <!-- Colonna destra - Form di contatto -->
                <div class="col-lg-6 offset-lg-1">
                    <h2 class="mb-4">Inviaci un messaggio</h2>
                    <div class="contact-form">
                        <form action="invia_contatto.php" method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Telefono (opzionale)</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="mb-4">
                                <label for="messaggio" class="form-label">Messaggio</label>
                                <textarea class="form-control" id="messaggio" name="messaggio" rows="5" required></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="privacy" required>
                                <label class="form-check-label small" for="privacy">Acconsento al trattamento dei dati personali ai sensi del GDPR</label>
                            </div>
                            <button type="submit" class="btn w-100" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">Invia messaggio</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mappa -->
    <section class="py-5" style="background-color: #f8f6ec;">
        <div class="container text-center">
            <h2 class="mb-5">Come Raggiungerci</h2>
            <div class="ratio ratio-21x9" style="max-height: 450px;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d45738.70303657223!2d11.677535699999999!3d44.3392429!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x132b4a2833cf5b33%3A0x27647e34318fc8c0!2s40026%20Imola%20BO!5e0!3m2!1sit!2sit!4v1683896054961!5m2!1sit!2sit" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

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
</body>

</html>
