<?php
require_once 'config.php';

$message = "";
$success = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Connessione al database
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception("Connessione database non disponibile");
        }
        
        // Cerca l'utente con questo token
        $stmt = $pdo->prepare("SELECT id, nome, email FROM utenti WHERE activation_token = ? AND attivo = 0");
        $stmt->execute([$token]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utente) {
            // Attiva l'account
            $stmt = $pdo->prepare("UPDATE utenti SET attivo = 1, activation_token = NULL, data_attivazione = NOW() WHERE id = ?");
            $stmt->execute([$utente['id']]);
            
            $success = true;
            $message = "Account attivato con successo! Ora puoi effettuare il login.";
            
        } else {
            $message = "Token di attivazione non valido o account già attivato.";
        }
        
    } catch (PDOException $e) {
        $message = "Errore del sistema. Riprova più tardi.";
    }
    
} else {
    $message = "Token di attivazione mancante.";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attivazione Account - Tenuta Manarese</title>
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
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <section class="py-5 bg-white" style="min-height: 60vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center">
                        <?php if ($success): ?>
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-success mb-4">Attivazione Completata!</h2>
                            <p class="lead mb-4"><?php echo htmlspecialchars($message); ?></p>
                            <a href="login.php" class="btn" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                <i class="fas fa-sign-in-alt me-2"></i>Vai al Login
                            </a>
                        <?php else: ?>
                            <div class="mb-4">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-warning mb-4">Errore di Attivazione</h2>
                            <p class="lead mb-4"><?php echo htmlspecialchars($message); ?></p>
                            <a href="login.php" class="btn me-2" style="background-color: var(--color-primary); border-color: var(--color-primary); color: white;">
                                <i class="fas fa-sign-in-alt me-2"></i>Vai al Login
                            </a>
                            <a href="contattaci.php" class="btn" style="background-color: var(--color-secondary); border-color: var(--color-secondary); color: white;">
                                <i class="fas fa-envelope me-2"></i>Contattaci
                            </a>
                        <?php endif; ?>
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
                <p class="mb-0 text-color-secondary">&copy; 2025 Tenuta Manarese. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>
</body>
</html>
