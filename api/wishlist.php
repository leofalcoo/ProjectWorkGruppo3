<?php
/**
 * API Gestione Wishlist (Lista Desideri)
 * Tenuta Manarese E-commerce
 */

require_once '../config.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Login richiesto']);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    switch ($action) {
        case 'toggle':
            echo json_encode(toggleWishlist($pdo, $userId, $input));
            break;
            
        case 'add':
            echo json_encode(addToWishlist($pdo, $userId, $input));
            break;
            
        case 'remove':
            echo json_encode(removeFromWishlist($pdo, $userId, $input));
            break;
            
        case 'get':
            echo json_encode(getWishlist($pdo, $userId));
            break;
            
        case 'get_status':
            echo json_encode(getWishlistStatus($pdo, $userId));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    error_log("Errore API Wishlist: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore del server']);
}

/**
 * Aggiunge/rimuove prodotto dalla wishlist
 */
function toggleWishlist($pdo, $userId, $data) {
    $productId = (int)($data['product_id'] ?? 0);
    
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Dati non validi'];
    }
    
    // Verifica se prodotto esiste
    $stmt = $pdo->prepare("SELECT nome FROM prodotti WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        return ['success' => false, 'message' => 'Prodotto non trovato'];
    }
    
    // Verifica se già in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE utente_id = ? AND prodotto_id = ?");
    $stmt->execute([$userId, $productId]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        // Rimuovi dalla wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE utente_id = ? AND prodotto_id = ?");
        $stmt->execute([$userId, $productId]);
        
        return [
            'success' => true,
            'message' => 'Rimosso dalla lista desideri',
            'in_wishlist' => false
        ];
    } else {
        // Aggiungi alla wishlist
        $stmt = $pdo->prepare("INSERT INTO wishlist (utente_id, prodotto_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        
        return [
            'success' => true,
            'message' => 'Aggiunto alla lista desideri',
            'in_wishlist' => true
        ];
    }
}

/**
 * Aggiunge prodotto alla wishlist
 */
function addToWishlist($pdo, $userId, $data) {
    $productId = (int)($data['product_id'] ?? 0);
    
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Dati non validi'];
    }
    
    // Verifica se prodotto esiste
    $stmt = $pdo->prepare("SELECT nome FROM prodotti WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        return ['success' => false, 'message' => 'Prodotto non trovato'];
    }
    
    // Verifica se già in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE utente_id = ? AND prodotto_id = ?");
    $stmt->execute([$userId, $productId]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Prodotto già nella lista desideri'];
    }
    
    // Aggiungi alla wishlist
    $stmt = $pdo->prepare("INSERT INTO wishlist (utente_id, prodotto_id) VALUES (?, ?)");
    $stmt->execute([$userId, $productId]);
    
    return [
        'success' => true,
        'message' => 'Prodotto aggiunto alla lista desideri'
    ];
}

/**
 * Rimuove prodotto dalla wishlist
 */
function removeFromWishlist($pdo, $userId, $data) {
    $productId = (int)($data['product_id'] ?? 0);
    
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Dati non validi'];
    }
    
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE utente_id = ? AND prodotto_id = ?");
    $stmt->execute([$userId, $productId]);
    
    return [
        'success' => true,
        'message' => 'Prodotto rimosso dalla lista desideri'
    ];
}

/**
 * Ottiene tutti i prodotti nella wishlist
 */
function getWishlist($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT w.*, p.nome, p.descrizione_breve, p.prezzo, p.prezzo_scontato, 
               p.immagine_principale, p.giacenza, p.disponibile, c.nome as categoria_nome
        FROM wishlist w
        JOIN prodotti p ON w.prodotto_id = p.id
        LEFT JOIN categorie c ON p.categoria_id = c.id
        WHERE w.utente_id = ?
        ORDER BY w.data_aggiunta DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    
    return [
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ];
}

/**
 * Ottiene lo stato wishlist per prodotti (per icone)
 */
function getWishlistStatus($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT prodotto_id FROM wishlist WHERE utente_id = ?");
    $stmt->execute([$userId]);
    $products = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return [
        'success' => true,
        'products' => $products
    ];
}
?>
