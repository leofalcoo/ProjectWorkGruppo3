<?php
/**
 * API Newsletter
 * Tenuta Manarese E-commerce
 */

require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non permesso']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email non valida']);
    exit;
}

try {
    $pdo = getDbConnection();
    
    // Verifica se email già registrata
    $stmt = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Utente esistente - aggiorna preferenze newsletter
        $stmt = $pdo->prepare("UPDATE utenti SET newsletter = 1 WHERE email = ?");
        $stmt->execute([$email]);
        $message = 'Preferenze newsletter aggiornate!';
    } else {
        // Nuovo iscritto - aggiungi alla lista newsletter (tabella separata)
        // Nota: Potresti voler creare una tabella newsletter separata
        $stmt = $pdo->prepare("
            INSERT INTO utenti (email, newsletter, data_registrazione) 
            VALUES (?, 1, NOW())
            ON DUPLICATE KEY UPDATE newsletter = 1
        ");
        $stmt->execute([$email]);
        $message = 'Iscrizione newsletter completata!';
    }
    
    // Log per statistiche
    error_log("Newsletter iscrizione: $email");
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    error_log("Errore newsletter: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore nell\'iscrizione']);
}
?>
