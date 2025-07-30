<?php
/**
 * API Gestione Carrello
 * Tenuta Manarese E-commerce
 */

require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDbConnection();
    
    switch ($action) {
        case 'add':
            echo json_encode(addToCart($pdo, $input));
            break;
            
        case 'update':
            echo json_encode(updateCartItem($pdo, $input));
            break;
            
        case 'remove':
            echo json_encode(removeFromCart($pdo, $input));
            break;
            
        case 'get':
            echo json_encode(getCart($pdo));
            break;
            
        case 'get_dropdown':
            echo json_encode(getCartDropdown($pdo));
            break;
            
        case 'clear':
            echo json_encode(clearCart($pdo));
            break;
            
        case 'count':
            echo json_encode(['count' => getCartCount()]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Azione non valida']);
    }
} catch (Exception $e) {
    error_log("Errore API Carrello: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore del server']);
}

/**
 * Aggiunge un prodotto al carrello
 */
function addToCart($pdo, $data) {
    $productId = (int)($data['product_id'] ?? 0);
    $quantity = (int)($data['quantity'] ?? 1);
    
    if ($productId <= 0 || $quantity <= 0) {
        return ['success' => false, 'message' => 'Dati non validi'];
    }
    
    // Verifica disponibilità prodotto
    $stmt = $pdo->prepare("SELECT nome, prezzo, prezzo_scontato, giacenza FROM prodotti WHERE id = ? AND disponibile = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        return ['success' => false, 'message' => 'Prodotto non trovato'];
    }
    
    if ($product['giacenza'] < $quantity) {
        return ['success' => false, 'message' => 'Quantità non disponibile'];
    }
    
    $price = $product['prezzo_scontato'] ?? $product['prezzo'];
    
    if (isUserLoggedIn()) {
        // Utente loggato - salva nel database
        $userId = $_SESSION['user_id'];
        
        // Verifica se il prodotto è già nel carrello
        $stmt = $pdo->prepare("SELECT quantita FROM carrello WHERE utente_id = ? AND prodotto_id = ?");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Aggiorna quantità esistente
            $newQuantity = $existing['quantita'] + $quantity;
            if ($newQuantity > $product['giacenza']) {
                return ['success' => false, 'message' => 'Quantità totale non disponibile'];
            }
            
            $stmt = $pdo->prepare("UPDATE carrello SET quantita = ?, data_modifica = NOW() WHERE utente_id = ? AND prodotto_id = ?");
            $stmt->execute([$newQuantity, $userId, $productId]);
        } else {
            // Inserisci nuovo item
            $stmt = $pdo->prepare("INSERT INTO carrello (utente_id, prodotto_id, quantita, prezzo_unitario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $productId, $quantity, $price]);
        }
    } else {
        // Utente ospite - usa sessione
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $newQuantity = $_SESSION['cart'][$productId] + $quantity;
            if ($newQuantity > $product['giacenza']) {
                return ['success' => false, 'message' => 'Quantità totale non disponibile'];
            }
            $_SESSION['cart'][$productId] = $newQuantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
    
    return [
        'success' => true,
        'message' => 'Prodotto aggiunto al carrello',
        'cart_count' => getCartCount()
    ];
}

/**
 * Aggiorna quantità di un item nel carrello
 */
function updateCartItem($pdo, $data) {
    $productId = (int)($data['product_id'] ?? 0);
    $quantity = (int)($data['quantity'] ?? 1);
    
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Dati non validi'];
    }
    
    if ($quantity <= 0) {
        return removeFromCart($pdo, ['product_id' => $productId]);
    }
    
    // Verifica disponibilità
    $stmt = $pdo->prepare("SELECT giacenza FROM prodotti WHERE id = ? AND disponibile = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product || $product['giacenza'] < $quantity) {
        return ['success' => false, 'message' => 'Quantità non disponibile'];
    }
    
    if (isUserLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("UPDATE carrello SET quantita = ?, data_modifica = NOW() WHERE utente_id = ? AND prodotto_id = ?");
        $stmt->execute([$quantity, $userId, $productId]);
    } else {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
    
    return [
        'success' => true,
        'message' => 'Carrello aggiornato',
        'cart_count' => getCartCount()
    ];
}

/**
 * Rimuove un prodotto dal carrello
 */
function removeFromCart($pdo, $data) {
    $productId = (int)($data['product_id'] ?? 0);
    
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Dati non validi'];
    }
    
    if (isUserLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("DELETE FROM carrello WHERE utente_id = ? AND prodotto_id = ?");
        $stmt->execute([$userId, $productId]);
    } else {
        unset($_SESSION['cart'][$productId]);
    }
    
    return [
        'success' => true,
        'message' => 'Prodotto rimosso dal carrello',
        'cart_count' => getCartCount()
    ];
}

/**
 * Ottiene tutto il carrello
 */
function getCart($pdo) {
    $items = [];
    $total = 0;
    
    if (isUserLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("
            SELECT c.*, p.nome, p.immagine_principale, p.giacenza, p.prezzo as prezzo_attuale,
                   (c.quantita * c.prezzo_unitario) as subtotale
            FROM carrello c 
            JOIN prodotti p ON c.prodotto_id = p.id 
            WHERE c.utente_id = ? AND p.disponibile = 1
            ORDER BY c.data_aggiunta DESC
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();
        
        foreach ($items as &$item) {
            $total += $item['subtotale'];
        }
    } else {
        if (!empty($_SESSION['cart'])) {
            $productIds = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            
            $stmt = $pdo->prepare("SELECT id, nome, prezzo, prezzo_scontato, immagine_principale, giacenza FROM prodotti WHERE id IN ($placeholders) AND disponibile = 1");
            $stmt->execute($productIds);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product) {
                $quantity = $_SESSION['cart'][$product['id']];
                $price = $product['prezzo_scontato'] ?? $product['prezzo'];
                $subtotale = $quantity * $price;
                
                $items[] = [
                    'prodotto_id' => $product['id'],
                    'nome' => $product['nome'],
                    'prezzo_unitario' => $price,
                    'quantita' => $quantity,
                    'subtotale' => $subtotale,
                    'immagine_principale' => $product['immagine_principale'],
                    'giacenza' => $product['giacenza']
                ];
                
                $total += $subtotale;
            }
        }
    }
    
    $shipping = calculateShipping($total);
    
    return [
        'success' => true,
        'items' => $items,
        'subtotal' => $total,
        'shipping' => $shipping,
        'total' => $total + $shipping,
        'count' => getCartCount()
    ];
}

/**
 * Ottiene HTML per dropdown carrello
 */
function getCartDropdown($pdo) {
    $cartData = getCart($pdo);
    $items = $cartData['items'];
    $count = $cartData['count'];
    
    if (empty($items)) {
        $html = '
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                <p class="mb-2">Il tuo carrello è vuoto</p>
                <a href="prodotti.php" class="btn btn-primary btn-sm">Continua lo Shopping</a>
            </div>';
    } else {
        $html = '<div class="p-2">';
        
        foreach (array_slice($items, 0, 3) as $item) {
            $html .= '
                <div class="cart-item d-flex">
                    <img src="' . htmlspecialchars($item['immagine_principale']) . '" 
                         alt="' . htmlspecialchars($item['nome']) . '" 
                         class="me-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                    <div class="flex-grow-1">
                        <div class="fw-bold small">' . htmlspecialchars($item['nome']) . '</div>
                        <div class="text-muted small">' . $item['quantita'] . ' × ' . formatPrice($item['prezzo_unitario']) . '</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold small">' . formatPrice($item['subtotale']) . '</div>
                    </div>
                </div>';
        }
        
        if (count($items) > 3) {
            $html .= '<div class="text-center text-muted small py-2">... e altri ' . (count($items) - 3) . ' prodotti</div>';
        }
        
        $html .= '
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold">
                <span>Totale:</span>
                <span>' . formatPrice($cartData['total']) . '</span>
            </div>
            <div class="d-grid gap-2 mt-3">
                <a href="carrello.php" class="btn btn-primary btn-sm">Vedi Carrello</a>
                <a href="checkout.php" class="btn btn-success btn-sm">Procedi all\'Ordine</a>
            </div>
        </div>';
    }
    
    return [
        'success' => true,
        'html' => $html,
        'count' => $count
    ];
}

/**
 * Svuota il carrello
 */
function clearCart($pdo) {
    if (isUserLoggedIn()) {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("DELETE FROM carrello WHERE utente_id = ?");
        $stmt->execute([$userId]);
    } else {
        $_SESSION['cart'] = [];
    }
    
    return [
        'success' => true,
        'message' => 'Carrello svuotato',
        'cart_count' => 0
    ];
}
?>
