<?php
// gestione.php - Pannello di gestione admin
session_start();
require_once 'config.php';

// Controlla se l'utente è loggato e admin
requireAdmin();

$user_id = getUserId();
$user_data = getUserData();

// Configurazione database
$host = "projectworkgruppo3.altervista.org";
$user = "projectworkgruppo3"; 
$password = "bBV3Bap5HnZc"; 
$dbname = "my_projectworkgruppo3";

$message = '';
$messageType = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ottieni statistiche generali
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM utenti WHERE ruolo = 'cliente'");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM ordini");
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
    
    $stmt = $pdo->query("SELECT SUM(totale) as total_revenue FROM ordini WHERE stato != 'cancelled'");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
    
    // Ottieni tutti gli ordini con dettagli utente
    $stmt = $pdo->prepare("
        SELECT o.*, u.nome, u.cognome, u.email, 
               COUNT(od.id) as num_prodotti,
               SUM(od.quantita) as total_quantita
        FROM ordini o 
        JOIN utenti u ON o.utente_id = u.id 
        LEFT JOIN ordini_dettagli od ON o.id = od.ordine_id 
        GROUP BY o.id 
        ORDER BY o.data_ordine DESC 
        LIMIT 50
    ");
    $stmt->execute();
    $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ottieni tutti gli utenti con statistiche
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(o.id) as num_ordini,
               SUM(o.totale) as totale_speso,
               MAX(o.data_ordine) as ultimo_ordine,
               COUNT(la.id) as login_attempts
        FROM utenti u 
        LEFT JOIN ordini o ON u.id = o.utente_id 
        LEFT JOIN login_attempts la ON u.id = la.user_id 
        WHERE u.ruolo = 'cliente'
        GROUP BY u.id 
        ORDER BY u.creato_il DESC
    ");
    $stmt->execute();
    $utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gestione aggiornamento stato ordine
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
        $ordine_id = $_POST['ordine_id'];
        $nuovo_stato = $_POST['nuovo_stato'];
        
        try {
            $stmt = $pdo->prepare("UPDATE ordini SET stato = ?, data_aggiornamento = NOW() WHERE id = ?");
            $stmt->execute([$nuovo_stato, $ordine_id]);
            
            $message = "Stato ordine aggiornato con successo!";
            $messageType = "success";
            
            // Ricarica ordini
            $stmt = $pdo->prepare("
                SELECT o.*, u.nome, u.cognome, u.email, 
                       COUNT(od.id) as num_prodotti,
                       SUM(od.quantita) as total_quantita
                FROM ordini o 
                JOIN utenti u ON o.utente_id = u.id 
                LEFT JOIN ordini_dettagli od ON o.id = od.ordine_id 
                GROUP BY o.id 
                ORDER BY o.data_ordine DESC 
                LIMIT 50
            ");
            $stmt->execute();
            $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $message = "Errore nell'aggiornamento dello stato";
            $messageType = "danger";
        }
    }
    
} catch (PDOException $e) {
    $message = "Errore di connessione al database";
    $messageType = "danger";
}

$page_title = "Gestione Admin - Tenuta Manarese";
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
                    <li class="nav-item"><a class="nav-link active" href="gestione.php">Gestione</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="py-5" style="background-color: #f8f6ec;">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-3 text-color-primary">
                <i class="fas fa-cogs me-3"></i>Pannello di Gestione
            </h1>
            <p class="lead text-color-dark">Gestisci ordini, utenti e statistiche del sito</p>
        </div>
    </section>

    <!-- Contenuto Gestione -->
    <section class="py-5">
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistiche Generali -->
            <div class="row mb-5">
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x mb-2" style="color: var(--color-primary);"></i>
                            <h3 class="mb-1"><?php echo $total_users; ?></h3>
                            <p class="text-muted mb-0">Clienti Registrati</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-shopping-bag fa-2x mb-2" style="color: var(--color-secondary);"></i>
                            <h3 class="mb-1"><?php echo $total_orders; ?></h3>
                            <p class="text-muted mb-0">Ordini Totali</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-euro-sign fa-2x mb-2" style="color: #28a745;"></i>
                            <h3 class="mb-1">€<?php echo number_format($total_revenue, 0); ?></h3>
                            <p class="text-muted mb-0">Fatturato Totale</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x mb-2" style="color: #dc3545;"></i>
                            <h3 class="mb-1">€<?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0'; ?></h3>
                            <p class="text-muted mb-0">Ordine Medio</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs per Gestione -->
            <ul class="nav nav-tabs" id="gestioneTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ordini-tab" data-bs-toggle="tab" data-bs-target="#ordini" 
                            type="button" role="tab" style="color: var(--color-primary);">
                        <i class="fas fa-shopping-bag me-2" style="color: var(--color-primary);"></i>Ordini
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="utenti-tab" data-bs-toggle="tab" data-bs-target="#utenti" 
                            type="button" role="tab" style="color: var(--color-primary);">
                        <i class="fas fa-users me-2" style="color: var(--color-primary);"></i>Utenti
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="gestioneTabContent">
                <!-- Tab Ordini -->
                <div class="tab-pane fade show active" id="ordini" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Gestione Ordini</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ordine</th>
                                            <th>Cliente</th>
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
                                                <td>
                                                    <div>
                                                        <?php echo htmlspecialchars($ordine['nome'] . ' ' . $ordine['cognome']); ?>
                                                    </div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($ordine['email']); ?></small>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($ordine['data_ordine'])); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="ordine_id" value="<?php echo $ordine['id']; ?>">
                                                        <select name="nuovo_stato" class="form-select form-select-sm" 
                                                                onchange="this.form.submit()">
                                                            <option value="pending" <?php echo $ordine['stato'] === 'pending' ? 'selected' : ''; ?>>In Attesa</option>
                                                            <option value="confirmed" <?php echo $ordine['stato'] === 'confirmed' ? 'selected' : ''; ?>>Confermato</option>
                                                            <option value="processing" <?php echo $ordine['stato'] === 'processing' ? 'selected' : ''; ?>>In Lavorazione</option>
                                                            <option value="shipped" <?php echo $ordine['stato'] === 'shipped' ? 'selected' : ''; ?>>Spedito</option>
                                                            <option value="delivered" <?php echo $ordine['stato'] === 'delivered' ? 'selected' : ''; ?>>Consegnato</option>
                                                            <option value="cancelled" <?php echo $ordine['stato'] === 'cancelled' ? 'selected' : ''; ?>>Annullato</option>
                                                        </select>
                                                        <input type="hidden" name="update_order_status" value="1">
                                                    </form>
                                                </td>
                                                <td><?php echo $ordine['num_prodotti']; ?></td>
                                                <td><strong>€<?php echo number_format($ordine['totale'], 2); ?></strong></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewOrderDetails(<?php echo $ordine['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Utenti -->
                <div class="tab-pane fade" id="utenti" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Gestione Utenti</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Telefono</th>
                                            <th>Registrazione</th>
                                            <th>Ordini</th>
                                            <th>Totale Speso</th>
                                            <th>Login</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($utenti as $utente): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?></strong>
                                                    </div>
                                                    <small class="text-muted">ID: <?php echo $utente['id']; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($utente['email']); ?></td>
                                                <td><?php echo htmlspecialchars($utente['telefono'] ?: 'N/A'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($utente['creato_il'])); ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $utente['num_ordini']; ?></span>
                                                </td>
                                                <td>
                                                    <strong>€<?php echo number_format($utente['totale_speso'] ?: 0, 2); ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <small class="text-muted">
                                                            Tentativi: <?php echo $utente['login_attempts']; ?>
                                                        </small>
                                                    </div>
                                                    <?php if ($utente['ultimo_accesso']): ?>
                                                        <small class="text-muted">
                                                            Ultimo: <?php echo date('d/m/Y', strtotime($utente['ultimo_accesso'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
    <script>
        function viewOrderDetails(orderId) {
            // Implementazione futura per visualizzare dettagli ordine
            alert('Funzionalità in arrivo: dettagli ordine #' + orderId);
        }
    </script>
</body>
</html>
