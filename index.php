<?php
// Configurazione di base
$page_title = "Tenuta Manarese - Home";
$current_page = "home";

// Avvia la sessione
session_start();

// Include configurazione principale
require_once 'config.php';

$user_logged_in = isLoggedIn();
$carrello_count = $user_logged_in ? getCartCount() : 0;

// DEBUG: rimuovere dopo il test
// echo "DEBUG: user_logged_in = " . ($user_logged_in ? 'true' : 'false') . "<br>";
// echo "DEBUG: SESSION = " . print_r($_SESSION, true) . "<br>";
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
    <script src="js/cookie_manager.js"></script>
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

    <!-- Hero -->
    <header class="hero" style="background-image: url('immagini/autunno.jpg');">
        <div class="container text-center hero-content">
            <h1 class="display-3 fw-bold mb-4 text-color-secondary">Benvenuti alla Tenuta Manarese</h1>
            <p class="lead fs-4 mb-5">Passione per la terra, amore per la qualità</p>
            <a href="prodotti.php" class="btn btn-outline-light btn-lg">Scopri i nostri prodotti</a>
        </div>
    </header>

    <!-- Introduzione -->
    <section class="py-5 bg-white">
        <div class="container text-center">
            <h2 class="section-title">Tradizione contadina romagnola</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <p class="lead">Situata nelle prime colline imolesi, l'azienda agricola Tenuta Manarese si estende su circa 80 ettari di terreno fertile e generoso, dove coltiviamo con passione frutta di alta qualità e produciamo vini genuini dal carattere autentico.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- La Nostra Storia -->
    <section class="story-section">
        <div class="container">
            <h2 class="text-center section-title">La Nostra Storia</h2>
            
            <div class="row align-items-center mb-5">
                <div class="col-md-6">
                    <div class="gallery-container">
                        <div class="gallery-item">
                            <img src="immagini/vite.jpg" alt="Vigneti della tenuta">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3>Le Nostre Origini</h3>
                    <div class="divider"></div>
                    <p class="lead">La nostra azienda ha trovato la sua casa definitiva nel 1980, dove proseguiamo la tradizione agricola con lo stesso amore e dedizione dei nostri fondatori.</p>
                    <p>Il fondo è coltivato a vigneto e frutteto. Albicocche, pesche e susine coprono un periodo di raccolta che va dal 20 maggio ai primi di settembre, garantendo prodotti freschi e genuini durante tutta la stagione estiva.</p>
                </div>
            </div>

            <div class="row align-items-center mb-5">
                <div class="col-md-6 order-md-2">
                    <div class="gallery-container">
                        <div class="gallery-item">
                            <img src="immagini/carretto.jpg" alt="La nostra uva">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3>La Nostra Cantina</h3>
                    <div class="divider"></div>
                    <p class="lead">Nella nostra cantina predomina l'utilizzo di metodi tradizionali, come pressature soffici delle uve e controllo attento delle temperature.</p>
                    <p>Rimontaggi, travasi e filtrazioni sono compiuti solo con operazioni meccaniche per conservare la genuinità e le caratteristiche naturali del vino. Disponiamo di un vasto assortimento di vini da tavola prodotti da vigneti autoctoni e internazionali.</p>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="gallery-container">
                        <div class="gallery-item">
                            <img src="immagini/uva.jpg" alt="I nostri prodotti">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3>I Nostri Prodotti</h3>
                    <div class="divider"></div>
                    <p class="lead">La nostra azienda coltiva molti alberi da frutto e ortaggi, mettendo a disposizione dei nostri clienti una vasta gamma di prodotti freschi.</p>
                    <p>Offriamo prodotti di stagione come ciliegie, albicocche, pesche, prugne, pomodori e altri ortaggi, tutti coltivati con metodi che rispettano la terra e la tradizione. Oltre alla distribuzione presso cooperative locali, disponiamo di una vendita diretta presso la nostra sede.</p>
                    <a href="prodotti.php" class="btn mt-3 mx-auto text-center" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">Scopri i nostri vini</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container ">
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
                <div class="mt-2">
                    <a href="privacy_policy_simple.php" class="text-decoration-none me-3 text-white">
                        <i class="fas fa-shield-alt me-1"></i>Privacy Policy
                    </a>
                    <a href="gdpr_simple.php" class="text-decoration-none me-3 text-white">
                        <i class="fas fa-user-shield me-1"></i>I Tuoi Diritti
                    </a>
                    <a href="javascript:void(0)" onclick="cookieManager.showPreferences()" class="text-decoration-none text-white">
                        <i class="fas fa-cookie-bite me-1"></i>Cookie
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
