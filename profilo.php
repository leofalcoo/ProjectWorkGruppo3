<?php
require_once 'config.php';
require_once 'carrello.php';

// Configura sessione sicura e richiede login
configureSecureSessions();
requireSecureLogin();

$pdo = getDBConnection();
$user = getCurrentUser();
$user_id = getUserId();
$carrello_count = getCarrelloCount($user_id);
$message = '';
$messageType = '';

// Gestione aggiornamento profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Processa il form di aggiornamento profilo
    // ... resto del codice POST ...
}

try {
    // Ottieni dati utente completi
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->execute([$user_id]);
    $utente_completo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ottieni ordini utente
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(od.id) as num_prodotti 
        FROM ordini o 
        LEFT JOIN ordini_dettagli od ON o.id = od.ordine_id 
        WHERE o.utente_id = ? 
        GROUP BY o.id 
        ORDER BY o.data_ordine DESC
    ");
    $stmt->execute([$user_id]);
    $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcola statistiche
    $total_ordini = count($ordini);
    $total_speso = array_sum(array_column($ordini, 'totale'));
    
    // Gestione aggiornamento profilo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $nome = trim($_POST['nome'] ?? '');
        $cognome = trim($_POST['cognome'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $data_nascita = $_POST['data_nascita'] ?? null;
        
        // Validazione
        if (empty($nome) || empty($cognome)) {
            $message = "Nome e cognome sono obbligatori";
            $messageType = "danger";
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE utenti 
                    SET nome = ?, cognome = ?, telefono = ?, data_nascita = ?, aggiornato_il = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$nome, $cognome, $telefono, $data_nascita, $user_id]);
                
                // Aggiorna sessione
                $_SESSION['user_nome'] = $nome;
                $_SESSION['user_cognome'] = $cognome;
                
                $message = "Profilo aggiornato con successo!";
                $messageType = "success";
                
                // Ricarica dati
                $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
                $stmt->execute([$user_id]);
                $utente_completo = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                $message = "Errore nell'aggiornamento del profilo";
                $messageType = "danger";
            }
        }
    }
    
} catch (PDOException $e) {
    $message = "Errore di connessione al database";
    $messageType = "danger";
}

$page_title = "Il Mio Profilo - Tenuta Manarese";
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
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="carrello-badge">
                                    <?php echo $carrello_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="gestione.php">Gestione</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link active" href="profilo.php">Profilo</a></li>
                        <li class="nav-item"><a class="nav-link" href="gdpr_simple.php">
                            <i class="fas fa-user-shield me-1"></i>Privacy
                        </a></li>
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
                <i class="fas fa-user me-3"></i>Il Mio Profilo
            </h1>
            <p class="lead text-color-dark">Gestisci le tue informazioni personali e visualizza i tuoi ordini</p>
        </div>
    </section>

    <!-- Contenuto Profilo -->
    <section class="py-5">
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Informazioni Profilo -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header text-white" style="background-color: var(--color-primary);">
                            <h5 class="mb-0" style="color: white;"><i class="fas fa-user me-2" style="color: white;"></i>Informazioni Personali</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($utente_completo['nome'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cognome" class="form-label">Cognome *</label>
                                    <input type="text" class="form-control" id="cognome" name="cognome" 
                                           value="<?php echo htmlspecialchars($utente_completo['cognome'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($utente_completo['email'] ?? ''); ?>" readonly>
                                    <small class="text-muted">L'email non può essere modificata</small>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Telefono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($utente_completo['telefono'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="data_nascita" class="form-label">Data di Nascita</label>
                                    <input type="date" class="form-control" id="data_nascita" name="data_nascita" 
                                           value="<?php echo $utente_completo['data_nascita'] ?? ''; ?>">
                                </div>
                                <button type="submit" name="update_profile" class="btn w-100" 
                                        style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                    <i class="fas fa-save me-2"></i>Salva Modifiche
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiche Account -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header text-white" style="background-color: var(--color-primary);">
                            <h6 class="mb-0" style="color: white;"><i class="fas fa-chart-bar me-2" style="color: white;"></i>Statistiche Account</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="mb-1"><?php echo $total_ordini; ?></h4>
                                    <small class="text-muted">Ordini Totali</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-1">€<?php echo number_format($total_speso, 0); ?></h4>
                                    <small class="text-muted">Totale Speso</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Cliente dal <?php echo date('d/m/Y', strtotime($utente_completo['creato_il'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cronologia Ordini -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header text-white" style="background-color: var(--color-primary);">
                            <h5 class="mb-0" style="color: white;"><i class="fas fa-shopping-bag me-2" style="color: white;"></i>I Miei Ordini</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($ordini)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-bag text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">Nessun ordine ancora</h5>
                                    <p class="text-muted">Non hai ancora effettuato ordini. Esplora i nostri prodotti!</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ordine</th>
                                                <th>Data</th>
                                                <th>Stato</th>
                                                <th>Articoli</th>
                                                <th>Totale</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordini as $ordine): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($ordine['numero_ordine']); ?></strong>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($ordine['data_ordine'])); ?></td>
                                                    <td>
                                                        <?php
                                                        $stato_colors = [
                                                            'pending' => 'warning',
                                                            'confirmed' => 'info',
                                                            'processing' => 'primary',
                                                            'shipped' => 'success',
                                                            'delivered' => 'success',
                                                            'cancelled' => 'danger'
                                                        ];
                                                        $stato_names = [
                                                            'pending' => 'In Attesa',
                                                            'confirmed' => 'Confermato',
                                                            'processing' => 'In Lavorazione',
                                                            'shipped' => 'Spedito',
                                                            'delivered' => 'Consegnato',
                                                            'cancelled' => 'Annullato'
                                                        ];
                                                        $color = $stato_colors[$ordine['stato']] ?? 'secondary';
                                                        $name = $stato_names[$ordine['stato']] ?? $ordine['stato'];
                                                        ?>
                                                        <span class="badge bg-<?php echo $color; ?>"><?php echo $name; ?></span>
                                                    </td>
                                                    <td><?php echo $ordine['num_prodotti']; ?></td>
                                                    <td><strong>€<?php echo number_format($ordine['totale'], 2); ?></strong></td>
                                                    <td>
                                                        <a href="ordine-confermato.php?ordine=<?php echo $ordine['numero_ordine']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/cookie_manager.js"></script>
</body>
</html>
