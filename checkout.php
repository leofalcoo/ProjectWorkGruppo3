<?php
// checkout.php - Sistema di checkout semplificato
session_start();
require_once 'config.php';
require_once 'carrello.php';

// Controlla se l'utente è loggato
requireLogin();

$user_id = getUserId();
$user_data = getUserData();
$carrello_items = getCarrelloItems($user_id);
$carrello_total = getCarrelloTotal($user_id);
$carrello_count = getCarrelloCount($user_id);

// Se il carrello è vuoto, reindirizza
if (empty($carrello_items)) {
    header("Location: carrello_view.php?error=empty_cart");
    exit;
}

// Gestione invio ordine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_ordine'])) {
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Errore di connessione al database');
        }
        
        $pdo->beginTransaction();
        
        // Genera numero ordine
        $numero_ordine = 'ORD' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Calcola spese di spedizione (gratis sopra 50€)
        $spese_spedizione = $carrello_total >= 50 ? 0 : 5.00;
        $totale_finale = $carrello_total + $spese_spedizione;
        
        // Prepara indirizzi
        $indirizzo_spedizione = [
            'nome' => sanitizeInput($_POST['nome'] ?? $user_data['nome']),
            'cognome' => sanitizeInput($_POST['cognome'] ?? $user_data['cognome']),
            'indirizzo' => sanitizeInput($_POST['indirizzo'] ?? ''),
            'citta' => sanitizeInput($_POST['citta'] ?? ''),
            'cap' => sanitizeInput($_POST['cap'] ?? ''),
            'provincia' => sanitizeInput($_POST['provincia'] ?? '')
        ];
        
        // Inserisci ordine
        $stmt = $pdo->prepare("
            INSERT INTO ordini 
            (numero_ordine, utente_id, stato, subtotale, spese_spedizione, totale, 
             metodo_pagamento, stato_pagamento, indirizzo_spedizione, note) 
            VALUES (?, ?, 'confirmed', ?, ?, ?, ?, 'paid', ?, ?)
        ");
        
        $stmt->execute([
            $numero_ordine,
            $user_id,
            $carrello_total,
            $spese_spedizione,
            $totale_finale,
            sanitizeInput($_POST['metodo_pagamento'] ?? 'carta'),
            json_encode($indirizzo_spedizione),
            sanitizeInput($_POST['note'] ?? '')
        ]);
        
        $ordine_id = $pdo->lastInsertId();
        
        // Inserisci dettagli ordine
        $stmt = $pdo->prepare("
            INSERT INTO ordini_dettagli 
            (ordine_id, prodotto_id, nome_prodotto, prezzo_unitario, quantita, subtotale) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($carrello_items as $item) {
            $subtotale_item = $item['prodotto_prezzo'] * $item['quantita'];
            $stmt->execute([
                $ordine_id,
                $item['prodotto_id'],
                $item['prodotto_nome'],
                $item['prodotto_prezzo'],
                $item['quantita'],
                $subtotale_item
            ]);
        }
        
        // Svuota carrello
        svuotaCarrello($user_id);
        
        $pdo->commit();
        
        // Reindirizza a pagina di conferma
        header("Location: ordine-confermato.php?ordine=" . $numero_ordine);
        exit;
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $error_message = "Errore durante la creazione dell'ordine: " . $e->getMessage();
    }
}

$page_title = "Checkout - Tenuta Manarese";
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="py-5" style="background-color: #f8f6ec;">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-3 text-color-primary">
                <i class="fas fa-credit-card me-3"></i>Checkout
            </h1>
            <p class="lead text-color-dark">Completa il tuo ordine</p>
        </div>
    </section>

    <!-- Contenuto Checkout -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="checkout-form">
                <div class="row">
                    <!-- Dati Spedizione -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header text-white" style="background-color: var(--color-primary);">
                                <h5 class="mb-0" style="color: white;"><i class="fas fa-truck me-2" style="color: white;"></i>Dati di Spedizione</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome" class="form-label">Nome *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" 
                                               value="<?php echo htmlspecialchars($user_data['nome']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cognome" class="form-label">Cognome *</label>
                                        <input type="text" class="form-control" id="cognome" name="cognome" 
                                               value="<?php echo htmlspecialchars($user_data['cognome']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="indirizzo" class="form-label">Indirizzo *</label>
                                    <input type="text" class="form-control" id="indirizzo" name="indirizzo" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="citta" class="form-label">Città *</label>
                                        <input type="text" class="form-control" id="citta" name="citta" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="cap" class="form-label">CAP *</label>
                                        <input type="text" class="form-control" id="cap" name="cap" pattern="[0-9]{5}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="provincia" class="form-label">Provincia *</label>
                                        <input type="text" class="form-control" id="provincia" name="provincia" maxlength="2" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Metodo Pagamento -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header text-white" style="background-color: var(--color-primary);">
                                <h5 class="mb-0" style="color: white;"><i class="fas fa-credit-card me-2" style="color: white;"></i>Metodo di Pagamento</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo_pagamento" id="carta" value="carta" checked>
                                        <label class="form-check-label" for="carta">
                                            <i class="fas fa-credit-card me-2"></i>Carta di credito/debito
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo_pagamento" id="paypal" value="paypal">
                                        <label class="form-check-label" for="paypal">
                                            <i class="fab fa-paypal me-2"></i>PayPal
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="metodo_pagamento" id="bonifico" value="bonifico">
                                        <label class="form-check-label" for="bonifico">
                                            <i class="fas fa-university me-2"></i>Bonifico bancario
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Note aggiuntive</h6>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="note" rows="3" placeholder="Note per la consegna (opzionale)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Riepilogo Ordine -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header text-white" style="background-color: var(--color-primary);">
                                <h5 class="mb-0" style="color: white;"><i class="fas fa-receipt me-2" style="color: white;"></i>Riepilogo Ordine</h5>
                            </div>
                            <div class="card-body">
                                <!-- Prodotti -->
                                <?php foreach ($carrello_items as $item): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <div>
                                            <h6 class="mb-0 small"><?php echo htmlspecialchars($item['prodotto_nome']); ?></h6>
                                            <small class="text-muted">Qty: <?php echo $item['quantita']; ?></small>
                                        </div>
                                        <span>€<?php echo number_format($item['prodotto_prezzo'] * $item['quantita'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>

                                <hr>
                                
                                <!-- Totali -->
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotale:</span>
                                    <span>€<?php echo number_format($carrello_total, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Spedizione:</span>
                                    <span>
                                        <?php if ($carrello_total >= 50): ?>
                                            <span class="text-success">Gratuita</span>
                                        <?php else: ?>
                                            €5.00
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Totale:</strong>
                                    <strong style="color: var(--color-primary);">
                                        €<?php echo number_format($carrello_total + ($carrello_total >= 50 ? 0 : 5), 2); ?>
                                    </strong>
                                </div>

                                <button type="submit" name="conferma_ordine" class="btn btn-lg w-100 mb-3" 
                                        style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                    <i class="fas fa-check me-2"></i>Conferma Ordine
                                </button>

                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>Transazione sicura al 100%
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
