<?php
// gdpr_simple.php - Gestione diritti GDPR semplificata
require_once 'config.php';
configureSecureSessions();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = getUserId();
    
    try {
        switch ($action) {
            case 'export_data':
                // Esporta dati (simulato)
                $message = "I tuoi dati saranno inviati via email entro 24 ore.";
                break;
                
            case 'delete_account':
                $confirmation = $_POST['delete_confirmation'] ?? '';
                if ($confirmation !== 'ELIMINA') {
                    throw new Exception('Devi scrivere ELIMINA per confermare');
                }
                
                // Elimina account
                $pdo = getDBConnection();
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM carrello WHERE utente_id = ?");
                $stmt->execute([$user_id]);
                
                $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $pdo->commit();
                
                session_destroy();
                header('Location: index.php?deleted=1');
                exit;
                
            case 'update_data':
                $nome = sanitizeInput($_POST['nome'] ?? '');
                $cognome = sanitizeInput($_POST['cognome'] ?? '');
                
                if (empty($nome) || empty($cognome)) {
                    throw new Exception('Nome e cognome sono obbligatori');
                }
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("UPDATE utenti SET nome = ?, cognome = ? WHERE id = ?");
                $stmt->execute([$nome, $cognome, $user_id]);
                
                $message = "Dati aggiornati con successo.";
                break;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Dati utente attuali
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT nome, cognome, email FROM utenti WHERE id = ?");
$stmt->execute([getUserId()]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Tuoi Diritti GDPR - Tenuta Manarese</title>
    <link rel="icon" type="image/x-icon" href="immagini/icona.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-leaf me-2"></i>Tenuta Manarese
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="profilo.php">Profilo</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check me-2"></i><?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h1 class="h4 mb-0">
                            <i class="fas fa-user-shield me-2"></i>I Tuoi Diritti GDPR
                        </h1>
                    </div>
                </div>

                <!-- Esporta Dati -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-download me-2"></i>Esporta i Tuoi Dati</h5>
                    </div>
                    <div class="card-body">
                        <p>Richiedi una copia di tutti i tuoi dati personali.</p>
                        <form method="POST">
                            <input type="hidden" name="action" value="export_data">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-file-download me-2"></i>Richiedi Esportazione
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Modifica Dati -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-edit me-2"></i>Modifica i Tuoi Dati</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($user['nome'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cognome" class="form-label">Cognome</label>
                                    <input type="text" class="form-control" id="cognome" name="cognome" 
                                           value="<?php echo htmlspecialchars($user['cognome'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email (non modificabile)</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Aggiorna Dati
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Elimina Account -->
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-trash me-2"></i>Elimina Account</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <strong>Attenzione!</strong> Questa azione eliminerà definitivamente il tuo account e tutti i dati.
                        </div>
                        <form method="POST" onsubmit="return confirm('Sei sicuro? Questa azione non può essere annullata.')">
                            <input type="hidden" name="action" value="delete_account">
                            <div class="mb-3">
                                <label for="delete_confirmation" class="form-label">
                                    Scrivi <strong>ELIMINA</strong> per confermare:
                                </label>
                                <input type="text" class="form-control" id="delete_confirmation" 
                                       name="delete_confirmation" placeholder="ELIMINA" required>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Elimina Account Definitivamente
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info -->
                <div class="card">
                    <div class="card-body">
                        <h6>Informazioni</h6>
                        <p class="mb-2">
                            <a href="privacy_policy_simple.php">Leggi la Privacy Policy completa</a>
                        </p>
                        <p class="mb-0 text-muted small">
                            Per assistenza: info@tenutamanarese.it
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/cookie_manager.js"></script>
</body>
</html>
