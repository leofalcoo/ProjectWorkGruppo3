<?php
session_start();
require_once 'config.php';
requireLogin();

$page_title = "I Miei Ordini - Tenuta Manarese";
$current_page = "ordini";

$user_id = getUserId();
$pdo = getDBConnection();

// Recupera ordini dell'utente
try {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(od.id) as num_prodotti, SUM(od.quantita * od.prezzo) as totale
        FROM ordini o 
        LEFT JOIN ordini_dettagli od ON o.id = od.ordine_id 
        WHERE o.utente_id = ? 
        GROUP BY o.id 
        ORDER BY o.data_ordine DESC
    ");
    $stmt->execute([$user_id]);
    $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ordini = [];
    $error_message = "Errore nel recupero degli ordini.";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="immagini/icona.ico" alt="Tenuta Manarese" width="40" height="40" class="d-inline-block align-text-top me-2">
                Tenuta Manarese
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="prodotti.php">Prodotti</a></li>
                    <li class="nav-item"><a class="nav-link" href="contattaci.php">Contattaci</a></li>
                    <li class="nav-item"><a class="nav-link" href="carrello_view.php">Carrello</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profilo.php">Profilo</a></li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="gestione.php">Gestione</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="mb-4">I Miei Ordini</h1>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if (empty($ordini)): ?>
                    <div class="alert alert-info">
                        <h5>Nessun ordine trovato</h5>
                        <p>Non hai ancora effettuato nessun ordine. <a href="prodotti.php">Inizia a fare shopping!</a></p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($ordini as $ordine): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <strong>Ordine #<?php echo $ordine['id']; ?></strong>
                                        <span class="badge bg-<?php echo $ordine['stato'] == 'completato' ? 'success' : ($ordine['stato'] == 'in_elaborazione' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($ordine['stato']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($ordine['data_ordine'])); ?></p>
                                        <p><strong>Prodotti:</strong> <?php echo $ordine['num_prodotti']; ?></p>
                                        <p><strong>Totale:</strong> €<?php echo number_format($ordine['totale'], 2, ',', '.'); ?></p>
                                        
                                        <?php if ($ordine['indirizzo_spedizione']): ?>
                                            <p><strong>Spedizione:</strong> <?php echo htmlspecialchars($ordine['indirizzo_spedizione']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="profilo.php" class="btn btn-secondary">Torna al Profilo</a>
                    <a href="prodotti.php" class="btn btn-primary">Continua Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Tenuta Manarese</h5>
                    <p>Vini di qualità dalle colline marchigiane</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-color-secondary">&copy; <?php echo date('Y'); ?> Tenuta Manarese. Tutti i diritti riservati.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
