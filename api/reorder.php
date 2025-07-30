<?php
/**
 * API Riordina Prodotti
 * Tenuta Manarese E-commerce
 */

require_once '../config.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Login richiesto']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non permesso']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($input['order_id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID ordine non valido']);
    exit;
}

try {
    $pdo = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    // Verifica che l'ordine appartenga all'utente
    $stmt = $pdo->prepare("SELECT id FROM ordini WHERE id = ? AND utente_id = ?");
    $stmt->execute([$orderId, $userId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ordine non trovato']);
        exit;
    }
    
    // Carica prodotti dell'ordine
    $stmt = $pdo->prepare("
        SELECT od.prodotto_id, od.quantita, p.nome, p.giacenza, p.disponibile,
               p.prezzo, p.prezzo_scontato
        FROM ordini_dettagli od
        JOIN prodotti p ON od.prodotto_id = p.id
        WHERE od.ordine_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Nessun prodotto trovato nell\'ordine']);
        exit;
    }
    
    $addedCount = 0;
    $notAvailable = [];
    
    foreach ($items as $item) {
        if (!$item['disponibile']) {
            $notAvailable[] = $item['nome'] . ' (non più disponibile)';
            continue;
        }
        
        if ($item['giacenza'] < $item['quantita']) {
            $notAvailable[] = $item['nome'] . ' (quantità limitata: ' . $item['giacenza'] . ' disponibili)';
            // Aggiungi quello disponibile
            if ($item['giacenza'] > 0) {
                addToCartReorder($pdo, $userId, $item['prodotto_id'], $item['giacenza'], $item);
                $addedCount++;
            }
            continue;
        }
        
        // Aggiungi al carrello
        addToCartReorder($pdo, $userId, $item['prodotto_id'], $item['quantita'], $item);
        $addedCount++;
    }
    
    $message = "Aggiunti $addedCount prodotti al carrello";
    if (!empty($notAvailable)) {
        $message .= ". Alcuni prodotti non erano disponibili: " . implode(', ', array_slice($notAvailable, 0, 3));
        if (count($notAvailable) > 3) {
            $message .= " e altri...";
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => getCartCount(),
        'added_count' => $addedCount,
        'not_available' => $notAvailable
    ]);
    
} catch (Exception $e) {
    error_log("Errore reorder: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore del server']);
}

/**
 * Aggiunge prodotto al carrello per reorder
 */
function addToCartReorder($pdo, $userId, $productId, $quantity, $productData) {
    $price = $productData['prezzo_scontato'] ?? $productData['prezzo'];
    
    // Verifica se già nel carrello
    $stmt = $pdo->prepare("SELECT quantita FROM carrello WHERE utente_id = ? AND prodotto_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Aggiorna quantità
        $newQuantity = min($existing['quantita'] + $quantity, $productData['giacenza']);
        $stmt = $pdo->prepare("UPDATE carrello SET quantita = ?, data_modifica = NOW() WHERE utente_id = ? AND prodotto_id = ?");
        $stmt->execute([$newQuantity, $userId, $productId]);
    } else {
        // Inserisci nuovo
        $stmt = $pdo->prepare("INSERT INTO carrello (utente_id, prodotto_id, quantita, prezzo_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $productId, $quantity, $price]);
    }
}
?>
