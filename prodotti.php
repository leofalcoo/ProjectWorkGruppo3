<?php
// Configurazione di base
$page_title = "I Nostri Vini - Tenuta Manarese";
$current_page = "prodotti";

// Include gestione sessioni e configurazione
session_start();
require_once 'config.php';

$user_logged_in = isLoggedIn();
$carrello_count = $user_logged_in ? getCarrelloCount(getUserId()) : 0;

// Recupera prodotti dal database
$pdo = getDBConnection();
$prodotti = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM prodotti WHERE disponibile = 1 ORDER BY nome");
        $prodotti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Errore recupero prodotti: " . $e->getMessage());
    }
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
            <?php else: ?>
                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'login' ? 'active' : ''; ?>" href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="hero" style="background-image: url('immagini/vino.jpg'); padding: 6rem 0;">
    <div class="container text-center hero-content">
      <h1 class="display-4 fw-bold mb-4 text-color-secondary">I Nostri Vini</h1>
      <p class="lead fs-5">Un'espressione autentica del nostro territorio</p>
    </div>
  </header>

  <!-- Introduzione -->
  <section class="py-5 bg-white">
    <div class="container text-center">
      <h2 class="section-title">La Nostra Selezione</h2>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <p class="lead">Ogni bottiglia racconta la storia della nostra terra, della nostra passione e della nostra esperienza. Scopri la nostra selezione di vini autentici, prodotti con amore e rispetto per la tradizione.</p>
        </div>
      </div>
      <?php if ($user_logged_in): ?>
        <div class="alert alert-info mt-4">
          <i class="fas fa-shopping-cart me-2"></i>
          <strong>Benvenuto!</strong> Aggiungi i tuoi vini preferiti al carrello per procedere all'acquisto.
          <a href="carrello_view.php" class="btn btn-sm ms-2" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">Visualizza Carrello</a>
        </div>
      <?php else: ?>
        <div class="alert alert-warning mt-4">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Effettua il login</strong> per aggiungere prodotti al carrello e procedere con l'acquisto.
          <a href="login.php" class="btn btn-sm ms-2" style="background-color: var(--color-secondary); border-color: var(--color-secondary); color: white;">Accedi</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Prodotti -->
  <section class="py-5" style="background-color: #f8f6ec;">
    <div class="container">
      <div class="row g-4">
        <?php if (empty($prodotti)): ?>
          <div class="col-12 text-center">
            <div class="alert alert-info">
              <h4>Nessun prodotto disponibile</h4>
              <p>Al momento non ci sono prodotti disponibili nel nostro catalogo.</p>
            </div>
          </div>
        <?php endif; ?>

        <?php foreach ($prodotti as $prodotto): ?>
          <div class="col-md-6 mb-4">
            <div class="card h-100">
              <div class="row g-0 h-100">
                <!-- Immagine bottiglia a sinistra -->
                <div class="col-md-4">
                  <img src="<?php echo htmlspecialchars($prodotto['immagine_principale'] ?? 'immagini/default.jpg'); ?>" 
                       class="img-fluid h-100 w-100" 
                       alt="<?php echo htmlspecialchars($prodotto['nome']); ?>"
                       style="object-fit: cover;">
                </div>
                
                <!-- Contenuto a destra -->
                <div class="col-md-8">
                  <div class="card-body d-flex flex-column h-100">
                    <h5 class="card-title" style="color: var(--color-primary);"><?php echo htmlspecialchars($prodotto['nome']); ?></h5>
                    
                    <?php if (!empty($prodotto['categoria'])): ?>
                      <p class="text-muted mb-0 small"><?php echo htmlspecialchars($prodotto['categoria']); ?></p>
                    <?php endif; ?>
                    
                    <div class="divider my-2"></div>
                    
                    <?php if (!empty($prodotto['descrizione_breve'])): ?>
                      <p class="card-text flex-grow-1"><?php echo htmlspecialchars($prodotto['descrizione_breve']); ?></p>
                    <?php elseif (!empty($prodotto['descrizione'])): ?>
                      <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($prodotto['descrizione'], 0, 100)) . '...'; ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-auto">
                      <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: var(--color-secondary);">
                          €<?php echo number_format($prodotto['prezzo_scontato'] ?? $prodotto['prezzo'], 2); ?>
                        </h5>
                        
                        <?php if ($user_logged_in): ?>
                          <button class="btn add-to-cart" 
                                  style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;"
                                  data-nome="<?php echo htmlspecialchars($prodotto['nome']); ?>"
                                  data-prezzo="<?php echo $prodotto['prezzo_scontato'] ?? $prodotto['prezzo']; ?>"
                                  data-id="<?php echo $prodotto['id']; ?>">
                            <i class="fas fa-shopping-cart me-1"></i> Aggiungi
                          </button>
                        <?php else: ?>
                          <a href="login.php" class="btn" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                            <i class="fas fa-sign-in-alt me-1"></i> Accedi
                          </a>
                        <?php endif; ?>
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

  <!-- Script AJAX per il carrello -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestione pulsanti aggiungi al carrello
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const nome = this.dataset.nome;
                const prezzo = this.dataset.prezzo;
                const id = this.dataset.id;
                
                // Disabilita il pulsante durante la richiesta
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Aggiungendo...';
                
                // Richiesta AJAX
                fetch('carrello.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&prodotto_nome=${encodeURIComponent(nome)}&prodotto_prezzo=${prezzo}&quantita=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Aggiorna il contatore del carrello
                        updateCartCounter();
                        
                        // Mostra messaggio di successo
                        showMessage('Prodotto aggiunto al carrello!', 'success');
                    } else {
                        showMessage(data.message || 'Errore durante l\'aggiunta al carrello', 'error');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    showMessage('Errore di connessione', 'error');
                })
                .finally(() => {
                    // Riabilita il pulsante
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-shopping-cart me-1"></i> Aggiungi';
                });
            });
        });
        
        // Funzione per mostrare messaggi
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
            
            // Rimuovi automaticamente dopo 3 secondi
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
