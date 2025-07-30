<?php
// ordine-confermato.php - Pagina di conferma ordine
session_start();
require_once 'config.php';

// Controlla se l'utente è loggato
requireLogin();

$user_id = getUserId();
$user_data = getUserData();
$numero_ordine = $_GET['ordine'] ?? '';

if (empty($numero_ordine)) {
    header('Location: profilo.php');
    exit;
}

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Errore di connessione al database');
    }
    
    // Verifica che l'ordine appartenga all'utente
    $stmt = $pdo->prepare("SELECT * FROM ordini WHERE numero_ordine = ? AND utente_id = ?");
    $stmt->execute([$numero_ordine, $user_id]);
    $ordine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ordine) {
        header('Location: profilo.php');
        exit;
    }
    
    // Ottieni dettagli ordine
    $stmt = $pdo->prepare("
        SELECT od.*, p.immagine_principale
        FROM ordini_dettagli od
        LEFT JOIN prodotti p ON od.prodotto_id = p.id
        WHERE od.ordine_id = ?
        ORDER BY od.id
    ");
    $stmt->execute([$ordine['id']]);
    $dettagli_ordine = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    header('Location: profilo.php');
    exit;
}

$page_title = "Ordine Confermato - Tenuta Manarese";
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="prodotti.php">Prodotti</a></li>
                    <li class="nav-item"><a class="nav-link" href="contattaci.php">Contattaci</a></li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="carrello_view.php">
                            <i class="fas fa-shopping-cart"></i> Carrello
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="gestione.php">Gestione</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="profilo.php">Profilo</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="py-5" style="background-color: #f8f6ec;">
        <div class="container text-center">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3 text-color-primary">
                Ordine Confermato!
            </h1>
            <p class="lead text-color-dark">Grazie per il tuo acquisto</p>
        </div>
    </section>

    <!-- Contenuto Conferma -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Info Ordine -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header text-white" style="background-color: var(--color-primary);">
                            <h5 class="mb-0" style="color: white;">
                                <i class="fas fa-receipt me-2" style="color: white;"></i>
                                Dettagli Ordine #<?php echo htmlspecialchars($ordine['numero_ordine']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Data Ordine:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($ordine['data_ordine'])); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Stato:</strong><br>
                                    <span class="badge bg-success">
                                        <?php echo ucfirst($ordine['stato']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Metodo di Pagamento:</strong><br>
                                    <?php echo ucfirst($ordine['metodo_pagamento']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Stato Pagamento:</strong><br>
                                    <span class="badge bg-success">
                                        <?php echo ucfirst($ordine['stato_pagamento']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php if (!empty($ordine['indirizzo_spedizione'])): 
                                $indirizzo = json_decode($ordine['indirizzo_spedizione'], true);
                            ?>
                            <div class="row">
                                <div class="col-12">
                                    <strong>Indirizzo di Spedizione:</strong><br>
                                    <?php echo htmlspecialchars($indirizzo['nome'] . ' ' . $indirizzo['cognome']); ?><br>
                                    <?php echo htmlspecialchars($indirizzo['indirizzo']); ?><br>
                                    <?php echo htmlspecialchars($indirizzo['cap'] . ' ' . $indirizzo['citta'] . ' (' . $indirizzo['provincia'] . ')'); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Prodotti Ordinati -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-box me-2"></i>Prodotti Ordinati
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($dettagli_ordine as $dettaglio): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <?php if (!empty($dettaglio['immagine_principale'])): ?>
                                        <img src="<?php echo htmlspecialchars($dettaglio['immagine_principale']); ?>" 
                                             class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div class="me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 60px; height: 60px; background-color: #f8f9fa; border-radius: 8px;">
                                            <i class="fas fa-wine-bottle text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($dettaglio['nome_prodotto']); ?></h6>
                                        <small class="text-muted">
                                            €<?php echo number_format($dettaglio['prezzo_unitario'], 2); ?> × <?php echo $dettaglio['quantita']; ?>
                                        </small>
                                    </div>
                                    
                                    <div class="text-end">
                                        <strong>€<?php echo number_format($dettaglio['subtotale'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Totali -->
                            <div class="pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotale:</span>
                                    <span>€<?php echo number_format($ordine['subtotale'], 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Spese di spedizione:</span>
                                    <span>
                                        <?php if ($ordine['spese_spedizione'] > 0): ?>
                                            €<?php echo number_format($ordine['spese_spedizione'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-success">Gratuita</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Totale:</strong>
                                    <strong style="color: var(--color-primary);">
                                        €<?php echo number_format($ordine['totale'], 2); ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prossimi Passi -->
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>Cosa succede ora?
                            </h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-envelope text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Email di Conferma</h6>
                                    <small class="text-muted">Riceverai una email di conferma all'indirizzo registrato</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-cogs text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Preparazione</h6>
                                    <small class="text-muted">Prepareremo il tuo ordine con cura entro 1-2 giorni lavorativi</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-shipping-fast text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Spedizione</h6>
                                    <small class="text-muted">Il tuo ordine sarà spedito e riceverai il tracking</small>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="profilo.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-user me-2"></i>I Miei Ordini
                                </a>
                                <a href="prodotti.php" class="btn" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                    <i class="fas fa-shopping-bag me-2"></i>Continua Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
