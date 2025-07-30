<?php
// Configurazione di base
$page_title = "Il Tuo Carrello - Tenuta Manarese";
$current_page = "carrello";

// Avvia la sessione
session_start();

// Include configurazione principale
require_once 'config.php';
require_once 'carrello.php';

// Controlla se l'utente è loggato
requireLogin();

$user_id = getUserId();
$carrello_items = getCarrelloItems($user_id);
$carrello_total = getCarrelloTotal($user_id);
$carrello_count = getCarrelloCount($user_id);
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
                        <a class="nav-link position-relative active" href="carrello_view.php">
                            <i class="fas fa-shopping-cart"></i> Carrello
                            <?php if ($carrello_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="carrello-badge">
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
                <i class="fas fa-shopping-cart me-3"></i>Il Tuo Carrello
            </h1>
            <p class="lead text-color-dark">Rivedi i tuoi prodotti e procedi all'acquisto</p>
        </div>
    </section>

    <!-- Contenuto Carrello -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($carrello_items)): ?>
                <!-- Carrello Vuoto -->
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <div class="card shadow-sm">
                            <div class="card-body py-5">
                                <i class="fas fa-shopping-cart text-muted mb-4" style="font-size: 4rem;"></i>
                                <h3 class="text-muted mb-3">Il tuo carrello è vuoto</h3>
                                <p class="text-muted mb-4">Non hai ancora aggiunto prodotti al carrello. Esplora la nostra selezione di vini pregiati!</p>
                                <a href="prodotti.php" class="btn btn-lg" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                    <i class="fas fa-wine-bottle me-2"></i>Scopri i nostri vini
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Carrello con Prodotti -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header text-white" style="background-color: var(--color-primary);">
                                <h5 class="mb-0 text-white">
                                    <i class="fas fa-list me-2"></i>Prodotti nel carrello
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($carrello_items as $index => $item): ?>
                                    <div class="cart-item p-4 <?php echo $index < count($carrello_items) - 1 ? 'border-bottom' : ''; ?>" 
                                         data-id="<?php echo $item['id']; ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <img src="<?php echo htmlspecialchars($item['immagine_principale'] ?? 'immagini/default.jpg'); ?>" 
                                                     class="img-fluid rounded" 
                                                     alt="<?php echo htmlspecialchars($item['prodotto_nome']); ?>"
                                                     style="max-height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['prodotto_nome']); ?></h6>
                                                <?php if (!empty($item['descrizione_breve'])): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($item['descrizione_breve'], 0, 60)) . '...'; ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <strong style="color: var(--color-secondary);">€<?php echo number_format($item['prodotto_prezzo'], 2); ?></strong>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary quantity-btn" type="button" data-action="decrease">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" class="form-control text-center quantity-input" 
                                                           value="<?php echo $item['quantita']; ?>" min="1" max="99" 
                                                           style="-webkit-appearance: none; -moz-appearance: textfield;">
                                                    <button class="btn btn-outline-secondary quantity-btn" type="button" data-action="increase">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <button class="btn btn-outline-danger btn-sm remove-item" 
                                                        data-id="<?php echo $item['id']; ?>" 
                                                        title="Rimuovi dal carrello">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Azioni Carrello -->
                        <div class="mt-3">
                            <a href="prodotti.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Continua lo shopping
                            </a>
                            <button class="btn btn-outline-danger ms-2" id="svuota-carrello">
                                <i class="fas fa-trash me-2"></i>Svuota carrello
                            </button>
                        </div>
                    </div>
                    
                    <!-- Riepilogo Ordine -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header text-white" style="background-color: var(--color-primary);">
                                <h5 class="mb-0 text-white">
                                    <i class="fas fa-calculator me-2"></i>Riepilogo Ordine
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotale:</span>
                                    <strong id="cart-subtotal">€<?php echo number_format($carrello_total, 2); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Spedizione:</span>
                                    <span class="text-muted">Calcolata al checkout</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Totale:</strong>
                                    <strong style="color: var(--color-primary);" id="cart-total">€<?php echo number_format($carrello_total, 2); ?></strong>
                                </div>
                                
                                <a href="checkout.php" class="btn btn-lg w-100 mb-3" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                    <i class="fas fa-credit-card me-2"></i>Procedi al Checkout
                                </a>
                                
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>Pagamenti sicuri
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Spedizione -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-truck me-2"></i>Informazioni Spedizione
                                </h6>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li><i class="fas fa-check me-2" style="color: var(--color-primary);"></i>Spedizione gratuita per ordini superiori a €50</li>
                                    <li><i class="fas fa-check me-2" style="color: var(--color-primary);"></i>Consegna in 2-3 giorni lavorativi</li>
                                    <li><i class="fas fa-check me-2" style="color: var(--color-primary);"></i>Imballaggio sicuro e professionale</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

    <!-- CSS per nascondere le freccine dell'input number -->
    <style>
        /* Nasconde le freccine in Chrome, Safari, Edge */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        /* Nasconde le freccine in Firefox */
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>

    <!-- Script per la gestione del carrello -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestione quantità
            document.querySelectorAll('.quantity-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const cartItem = this.closest('.cart-item');
                    const quantityInput = cartItem.querySelector('.quantity-input');
                    const cartId = cartItem.dataset.id;
                    
                    let newQuantity = parseInt(quantityInput.value);
                    
                    if (action === 'increase') {
                        newQuantity++;
                    } else if (action === 'decrease' && newQuantity > 1) {
                        newQuantity--;
                    }
                    
                    updateQuantity(cartId, newQuantity, cartItem);
                });
            });
            
            // Gestione input quantità diretta
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    const cartItem = this.closest('.cart-item');
                    const cartId = cartItem.dataset.id;
                    const newQuantity = parseInt(this.value) || 1;
                    
                    if (newQuantity < 1) {
                        this.value = 1;
                        return;
                    }
                    
                    updateQuantity(cartId, newQuantity, cartItem);
                });
            });
            
            // Gestione rimozione articoli
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    const cartId = this.dataset.id;
                    const cartItem = this.closest('.cart-item');
                    
                    if (confirm('Sei sicuro di voler rimuovere questo prodotto dal carrello?')) {
                        removeItem(cartId, cartItem);
                    }
                });
            });
            
            // Svuota carrello
            const svuotaBtn = document.getElementById('svuota-carrello');
            if (svuotaBtn) {
                svuotaBtn.addEventListener('click', function() {
                    if (confirm('Sei sicuro di voler svuotare completamente il carrello?')) {
                        window.location.reload();
                    }
                });
            }
            
            function updateQuantity(cartId, quantity, cartItem) {
                fetch('carrello.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update&carrello_id=${cartId}&quantita=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Aggiorna l'input
                        cartItem.querySelector('.quantity-input').value = quantity;
                        
                        // Aggiorna i totali
                        updateTotals();
                        updateCartCounter();
                    } else {
                        showMessage(data.message || 'Errore durante l\'aggiornamento', 'error');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showMessage('Errore di connessione', 'error');
                });
            }
            
            function removeItem(cartId, cartItem) {
                fetch('carrello.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&carrello_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cartItem.remove();
                        updateTotals();
                        updateCartCounter();
                        
                        // Se non ci sono più articoli, ricarica la pagina
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            window.location.reload();
                        }
                    } else {
                        showMessage(data.message || 'Errore durante la rimozione', 'error');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showMessage('Errore di connessione', 'error');
                });
            }
            
            function updateTotals() {
                let total = 0;
                document.querySelectorAll('.cart-item').forEach(item => {
                    const prezzoElement = item.querySelector('strong[style*="color: var(--color-secondary)"]');
                    const quantityInput = item.querySelector('.quantity-input');
                    const prezzo = parseFloat(prezzoElement.textContent.replace('€', ''));
                    const quantity = parseInt(quantityInput.value);
                    total += prezzo * quantity;
                });
                
                document.getElementById('cart-subtotal').textContent = '€' + total.toFixed(2);
                document.getElementById('cart-total').textContent = '€' + total.toFixed(2);
            }
            
            function updateCartCounter() {
                fetch('carrello.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_count'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById('carrello-badge');
                        if (badge) {
                            badge.textContent = data.count;
                            if (data.count === 0) {
                                badge.style.display = 'none';
                            } else {
                                badge.style.display = 'inline';
                            }
                        }
                    }
                });
            }
            
            function showMessage(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                         style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                        <i class="fas fa-${icon} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', alertHtml);
                
                setTimeout(() => {
                    const alert = document.querySelector('.alert:last-child');
                    if (alert) {
                        alert.remove();
                    }
                }, 3000);
            }
        });
    </script>

</body>
</html>
