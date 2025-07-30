<?php
require_once 'config.php';
requireLogin();

$pdo = getDbConnection();
$user = getCurrentUser();

// Carica wishlist utente
$stmt = $pdo->prepare("
    SELECT w.*, p.nome, p.descrizione_breve, p.prezzo, p.prezzo_scontato, 
           p.immagine_principale, p.giacenza, p.disponibile, c.nome as categoria_nome,
           p.caratteristiche
    FROM wishlist w
    JOIN prodotti p ON w.prodotto_id = p.id
    LEFT JOIN categorie c ON p.categoria_id = c.id
    WHERE w.utente_id = ?
    ORDER BY w.data_aggiunta DESC
");
$stmt->execute([$user['id']]);
$wishlistItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lista Desideri - Tenuta Manarese</title>
  <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  
  <style>
    .page-header {
      background: linear-gradient(135deg, #dc3545 0%, #e91e63 100%);
      color: white;
      padding: 3rem 0;
    }
    
    .wishlist-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
    }
    
    .wishlist-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .product-image {
      height: 250px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    
    .remove-wishlist {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(220, 53, 69, 0.9);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    
    .remove-wishlist:hover {
      background: #dc3545;
      transform: scale(1.1);
    }
    
    .availability-badge {
      position: absolute;
      bottom: 10px;
      left: 10px;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: bold;
    }
    
    .available {
      background: #d4edda;
      color: #155724;
    }
    
    .limited {
      background: #fff3cd;
      color: #856404;
    }
    
    .unavailable {
      background: #f8d7da;
      color: #721c24;
    }
    
    .price-section {
      background: linear-gradient(45deg, #8B4513, #A0522D);
      color: white;
      padding: 1rem;
      margin: -1rem -1rem 1rem -1rem;
    }
    
    .original-price {
      text-decoration: line-through;
      opacity: 0.7;
      font-size: 0.9rem;
    }
    
    .btn-add-cart {
      background: linear-gradient(45deg, #28a745, #20c997);
      border: none;
      transition: all 0.3s ease;
    }
    
    .btn-add-cart:hover {
      transform: translateY(-2px);
    }
    
    .btn-add-cart:disabled {
      background: #6c757d;
      transform: none;
    }
    
    .empty-wishlist {
      text-align: center;
      padding: 4rem 2rem;
    }
    
    .wishlist-actions {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      position: sticky;
      top: 20px;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="fas fa-wine-bottle me-2"></i>Tenuta Manarese
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="prodotti.php">I Nostri Vini</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contattaci.php">Contattaci</a>
        </li>
      </ul>
      
      <div class="d-flex align-items-center">
        <!-- Carrello -->
        <div class="dropdown me-3">
          <button class="btn btn-outline-light dropdown-toggle position-relative" type="button" 
                  id="cartDropdown" data-bs-toggle="dropdown">
            <i class="fas fa-shopping-cart"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                  id="cart-count"><?= getCartCount() ?></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end" style="min-width: 300px;" id="cart-dropdown-content">
            <!-- Contenuto carrello caricato via JS -->
          </div>
        </div>
        
        <!-- Menu utente -->
        <div class="dropdown">
          <button class="btn btn-outline-light dropdown-toggle" type="button" 
                  id="userDropdown" data-bs-toggle="dropdown">
            <i class="fas fa-user"></i> 
            <?= htmlspecialchars($user['nome'] . ' ' . $user['cognome']) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="profilo.php"><i class="fas fa-user me-2"></i>Il Mio Profilo</a></li>
            <li><a class="dropdown-item" href="ordini.php"><i class="fas fa-box me-2"></i>I Miei Ordini</a></li>
            <li><a class="dropdown-item active" href="wishlist.php"><i class="fas fa-heart me-2"></i>Lista Desideri</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Header pagina -->
<section class="page-header">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="display-5 fw-bold mb-2">
          <i class="fas fa-heart me-3"></i>
          La Mia Lista Desideri
        </h1>
        <p class="lead mb-0">I vini che ami, pronti per essere ordinati</p>
      </div>
      <div class="col-md-4 text-md-end">
        <div class="text-light">
          <div class="h4 mb-1"><?= count($wishlistItems) ?></div>
          <small>Prodotti salvati</small>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contenuto principale -->
<div class="container py-4">
  
  <?php if (empty($wishlistItems)): ?>
    <!-- Lista vuota -->
    <div class="empty-wishlist">
      <i class="fas fa-heart-broken fa-5x text-muted mb-4"></i>
      <h3 class="text-muted mb-3">La tua lista desideri è vuota</h3>
      <p class="text-muted mb-4">
        Aggiungi i tuoi vini preferiti alla lista desideri per trovarli facilmente in futuro
      </p>
      <a href="prodotti.php" class="btn btn-primary btn-lg">
        <i class="fas fa-wine-glass-alt me-2"></i>Scopri i Nostri Vini
      </a>
    </div>
  <?php else: ?>
    <div class="row">
      <!-- Colonna sinistra - Prodotti -->
      <div class="col-lg-9">
        <div class="row g-4">
          <?php foreach ($wishlistItems as $item): ?>
            <?php
              $caratteristiche = json_decode($item['caratteristiche'], true) ?? [];
              $hasDiscount = !empty($item['prezzo_scontato']);
              $finalPrice = $hasDiscount ? $item['prezzo_scontato'] : $item['prezzo'];
              $discount = $hasDiscount ? round((($item['prezzo'] - $item['prezzo_scontato']) / $item['prezzo']) * 100) : 0;
            ?>
            <div class="col-md-6 col-lg-4">
              <div class="card wishlist-card">
                <div class="product-image" style="background-image: url('<?= htmlspecialchars($item['immagine_principale']) ?>');">
                  <!-- Bottone rimuovi -->
                  <button class="remove-wishlist" onclick="removeFromWishlist(<?= $item['prodotto_id'] ?>)" 
                          title="Rimuovi dalla lista desideri">
                    <i class="fas fa-times"></i>
                  </button>
                  
                  <!-- Badge disponibilità -->
                  <?php if (!$item['disponibile']): ?>
                    <span class="availability-badge unavailable">Non Disponibile</span>
                  <?php elseif ($item['giacenza'] <= 5): ?>
                    <span class="availability-badge limited">Ultime <?= $item['giacenza'] ?> disponibili</span>
                  <?php else: ?>
                    <span class="availability-badge available">Disponibile</span>
                  <?php endif; ?>
                </div>
                
                <div class="card-body p-0">
                  <!-- Prezzo -->
                  <div class="price-section text-center">
                    <?php if ($hasDiscount): ?>
                      <div class="original-price"><?= formatPrice($item['prezzo']) ?></div>
                      <div class="h5 mb-0 fw-bold">
                        <?= formatPrice($finalPrice) ?>
                        <span class="badge bg-light text-dark ms-2">-<?= $discount ?>%</span>
                      </div>
                    <?php else: ?>
                      <div class="h5 mb-0 fw-bold"><?= formatPrice($finalPrice) ?></div>
                    <?php endif; ?>
                  </div>
                  
                  <div class="p-3">
                    <!-- Categoria -->
                    <div class="mb-2">
                      <span class="badge bg-secondary"><?= htmlspecialchars($item['categoria_nome']) ?></span>
                    </div>
                    
                    <!-- Nome prodotto -->
                    <h6 class="card-title mb-2">
                      <a href="prodotto-dettaglio.php?id=<?= $item['prodotto_id'] ?>" 
                         class="text-decoration-none text-dark">
                        <?= htmlspecialchars($item['nome']) ?>
                      </a>
                    </h6>
                    
                    <!-- Descrizione breve -->
                    <p class="card-text text-muted small mb-3">
                      <?= htmlspecialchars($item['descrizione_breve']) ?>
                    </p>
                    
                    <!-- Caratteristiche -->
                    <?php if (!empty($caratteristiche['vitigno'])): ?>
                      <small class="d-block text-muted mb-2">
                        <strong>Vitigno:</strong> <?= htmlspecialchars($caratteristiche['vitigno']) ?>
                      </small>
                    <?php endif; ?>
                    
                    <small class="text-muted">
                      Aggiunto il <?= date('d/m/Y', strtotime($item['data_aggiunta'])) ?>
                    </small>
                  </div>
                  
                  <!-- Azioni -->
                  <div class="card-footer bg-transparent border-0 p-3 pt-0">
                    <div class="d-grid gap-2">
                      <?php if ($item['disponibile'] && $item['giacenza'] > 0): ?>
                        <button class="btn btn-add-cart text-white" 
                                onclick="addToCart(<?= $item['prodotto_id'] ?>, 1)">
                          <i class="fas fa-cart-plus me-2"></i>Aggiungi al Carrello
                        </button>
                      <?php else: ?>
                        <button class="btn btn-add-cart text-white" disabled>
                          <i class="fas fa-times me-2"></i>Non Disponibile
                        </button>
                      <?php endif; ?>
                      
                      <div class="row g-1">
                        <div class="col-6">
                          <a href="prodotto-dettaglio.php?id=<?= $item['prodotto_id'] ?>" 
                             class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-eye"></i> Dettagli
                          </a>
                        </div>
                        <div class="col-6">
                          <button class="btn btn-outline-danger btn-sm w-100" 
                                  onclick="removeFromWishlist(<?= $item['prodotto_id'] ?>)">
                            <i class="fas fa-trash"></i> Rimuovi
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- Colonna destra - Azioni -->
      <div class="col-lg-3">
        <div class="wishlist-actions">
          <h5 class="mb-4">
            <i class="fas fa-tools me-2"></i>
            Azioni Rapide
          </h5>
          
          <!-- Statistiche -->
          <div class="mb-4">
            <div class="row text-center">
              <div class="col-6">
                <div class="h4 text-primary mb-1"><?= count($wishlistItems) ?></div>
                <small class="text-muted">Prodotti</small>
              </div>
              <div class="col-6">
                <div class="h4 text-success mb-1">
                  <?php
                    $totalValue = 0;
                    foreach ($wishlistItems as $item) {
                      $price = $item['prezzo_scontato'] ?? $item['prezzo'];
                      $totalValue += $price;
                    }
                    echo formatPrice($totalValue);
                  ?>
                </div>
                <small class="text-muted">Valore Tot.</small>
              </div>
            </div>
          </div>
          
          <!-- Azioni -->
          <div class="d-grid gap-2">
            <button class="btn btn-success" onclick="addAllToCart()" 
                    <?= empty($wishlistItems) ? 'disabled' : '' ?>>
              <i class="fas fa-cart-plus me-2"></i>
              Aggiungi Tutto al Carrello
            </button>
            
            <button class="btn btn-outline-primary" onclick="shareWishlist()">
              <i class="fas fa-share me-2"></i>
              Condividi Lista
            </button>
            
            <button class="btn btn-outline-danger" onclick="clearWishlist()" 
                    <?= empty($wishlistItems) ? 'disabled' : '' ?>>
              <i class="fas fa-trash me-2"></i>
              Svuota Lista
            </button>
          </div>
          
          <!-- Suggerimenti -->
          <div class="mt-4">
            <h6 class="text-muted">💡 Suggerimento</h6>
            <p class="small text-muted">
              I prodotti nella tua lista desideri rimangono salvati per sempre. 
              Riceverai notifiche quando ci sono offerte speciali sui tuoi vini preferiti!
            </p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-dark text-light py-4">
  <div class="container">
    <div class="row">
      <div class="col-md-6">
        <h5><i class="fas fa-wine-bottle me-2"></i>Tenuta Manarese</h5>
        <p class="small">Tradizione vinicola dal 1952. Vini di qualità dalle colline emiliane.</p>
      </div>
      <div class="col-md-6 text-md-end">
        <div class="social-links">
          <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
          <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
        </div>
        <p class="small mt-2">&copy; 2025 Tenuta Manarese. Tutti i diritti riservati.</p>
      </div>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Carica carrello dropdown
document.addEventListener('DOMContentLoaded', function() {
    loadCartDropdown();
});

function loadCartDropdown() {
    fetch('api/cart.php?action=get_dropdown')
    .then(response => response.json())
    .then(data => {
        document.getElementById('cart-dropdown-content').innerHTML = data.html;
    })
    .catch(error => console.error('Errore caricamento carrello:', error));
}

// Aggiungi al carrello
function addToCart(productId, quantity = 1) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-count').textContent = data.cart_count;
            loadCartDropdown();
            showNotification('Prodotto aggiunto al carrello!', 'success');
        } else {
            showNotification(data.message || 'Errore nell\'aggiunta al carrello', 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Rimuovi dalla wishlist
function removeFromWishlist(productId) {
    if (!confirm('Vuoi rimuovere questo prodotto dalla lista desideri?')) {
        return;
    }
    
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Ricarica la pagina
        } else {
            showNotification(data.message || 'Errore nella rimozione', 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Aggiungi tutto al carrello
function addAllToCart() {
    if (!confirm('Vuoi aggiungere tutti i prodotti disponibili al carrello?')) {
        return;
    }
    
    const productIds = <?= json_encode(array_column(array_filter($wishlistItems, function($item) { 
        return $item['disponibile'] && $item['giacenza'] > 0; 
    }), 'prodotto_id')) ?>;
    
    let promises = productIds.map(productId => {
        return fetch('api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: 1
            })
        });
    });
    
    Promise.all(promises)
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(results => {
        const successful = results.filter(r => r.success).length;
        if (successful > 0) {
            loadCartDropdown();
            document.getElementById('cart-count').textContent = results[results.length - 1].cart_count || 0;
            showNotification(`${successful} prodotti aggiunti al carrello!`, 'success');
        } else {
            showNotification('Nessun prodotto aggiunto', 'error');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Svuota wishlist
function clearWishlist() {
    if (!confirm('Sei sicuro di voler svuotare tutta la lista desideri? Questa azione non può essere annullata.')) {
        return;
    }
    
    const productIds = <?= json_encode(array_column($wishlistItems, 'prodotto_id')) ?>;
    
    let promises = productIds.map(productId => {
        return fetch('api/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        });
    });
    
    Promise.all(promises)
    .then(() => {
        location.reload();
    })
    .catch(error => {
        console.error('Errore:', error);
        showNotification('Errore nella rimozione', 'error');
    });
}

// Condividi wishlist
function shareWishlist() {
    if (navigator.share) {
        navigator.share({
            title: 'La mia Lista Desideri - Tenuta Manarese',
            text: 'Guarda i vini che ho selezionato dalla Tenuta Manarese!',
            url: window.location.href
        });
    } else {
        // Fallback per browser che non supportano Web Share API
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Link copiato negli appunti!', 'success');
        }).catch(() => {
            showNotification('Impossibile condividere', 'error');
        });
    }
}

// Mostra notifiche
function showNotification(message, type = 'info') {
    // Crea il container se non esiste
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show`;
    notification.style.minWidth = '300px';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>

</body>
</html>
