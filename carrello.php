<?php
// carrello.php - Gestione carrello con sistema relazionale
require_once 'config.php';

// Configura sessione sicura
configureSecureSessions();

// Gestione AJAX per le operazioni del carrello
// NON intercettare se la richiesta viene da checkout.php
if (($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['conferma_ordine'])) || 
    ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_count')) {
    header('Content-Type: application/json');
    
    // Gestione richieste GET per get_count
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get_count') {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Accesso richiesto']);
            exit;
        }
        
        try {
            $pdo = getDBConnection();
            if (!$pdo) {
                throw new Exception('Errore di connessione al database');
            }
            
            $user_id = getUserId();
            if (!$user_id) {
                throw new Exception('Errore: utente non loggato correttamente');
            }
            
            $stmt = $pdo->prepare("SELECT SUM(quantita) as total FROM carrello WHERE utente_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => intval($result['total'] ?? 0)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Debug: log dei dati ricevuti
    error_log('CARRELLO DEBUG - POST data: ' . print_r($_POST, true));
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Accesso richiesto']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    try {
        $pdo = getDBConnection();
        if (!$pdo) {
            throw new Exception('Errore di connessione al database - getDBConnection() returned false');
        }
        
        switch ($action) {
            case 'add':
                $prodotto_nome = sanitizeInput($_POST['prodotto_nome'] ?? '');
                $prodotto_prezzo = floatval($_POST['prodotto_prezzo'] ?? 0);
                $quantita = intval($_POST['quantita'] ?? 1);
                
                if (empty($prodotto_nome) || $prodotto_prezzo <= 0 || $quantita <= 0) {
                    throw new Exception('Dati prodotto non validi');
                }
                
                $user_id = getUserId();
                if (!$user_id) {
                    throw new Exception('Errore: utente non loggato correttamente');
                }
                
                // Trova il prodotto nella tabella prodotti per nome
                $stmt = $pdo->prepare("SELECT id FROM prodotti WHERE nome = ?");
                $stmt->execute([$prodotto_nome]);
                $prodotto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$prodotto) {
                    throw new Exception('Prodotto non trovato');
                }
                
                $prodotto_id = $prodotto['id'];
                
                // Verifica se il prodotto è già nel carrello
                $stmt = $pdo->prepare("SELECT id, quantita FROM carrello WHERE utente_id = ? AND prodotto_id = ?");
                $stmt->execute([$user_id, $prodotto_id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // Aggiorna quantità
                    $new_quantity = $existing['quantita'] + $quantita;
                    $stmt = $pdo->prepare("UPDATE carrello SET quantita = ?, data_modifica = NOW() WHERE id = ?");
                    $stmt->execute([$new_quantity, $existing['id']]);
                } else {
                    // Inserisci nuovo prodotto
                    $stmt = $pdo->prepare("INSERT INTO carrello (utente_id, prodotto_id, quantita) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $prodotto_id, $quantita]);
                }
                
                $response['success'] = true;
                $response['message'] = 'Prodotto aggiunto al carrello';
                break;
                
            case 'remove':
                $carrello_id = intval($_POST['carrello_id'] ?? 0);
                $user_id = getUserId();
                if (!$user_id) {
                    throw new Exception('Errore: utente non loggato correttamente');
                }
                
                $stmt = $pdo->prepare("DELETE FROM carrello WHERE id = ? AND utente_id = ?");
                $stmt->execute([$carrello_id, $user_id]);
                
                $response['success'] = true;
                $response['message'] = 'Prodotto rimosso dal carrello';
                break;
                
            case 'update':
                $carrello_id = intval($_POST['carrello_id'] ?? 0);
                $quantita = intval($_POST['quantita'] ?? 1);
                $user_id = getUserId();
                if (!$user_id) {
                    throw new Exception('Errore: utente non loggato correttamente');
                }
                
                if ($quantita <= 0) {
                    // Se quantità è 0 o negativa, rimuovi il prodotto
                    $stmt = $pdo->prepare("DELETE FROM carrello WHERE id = ? AND utente_id = ?");
                    $stmt->execute([$carrello_id, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE carrello SET quantita = ?, data_modifica = NOW() WHERE id = ? AND utente_id = ?");
                    $stmt->execute([$quantita, $carrello_id, $user_id]);
                }
                
                $response['success'] = true;
                $response['message'] = 'Carrello aggiornato';
                break;
                
            case 'get_count':
                $user_id = getUserId();
                if (!$user_id) {
                    throw new Exception('Errore: utente non loggato correttamente');
                }
                $stmt = $pdo->prepare("SELECT SUM(quantita) as total FROM carrello WHERE utente_id = ?");
                $stmt->execute([$user_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $response['success'] = true;
                $response['count'] = intval($result['total'] ?? 0);
                break;
                
            default:
                throw new Exception('Azione non valida');
        }
        
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Funzione per ottenere i prodotti nel carrello (con JOIN per ottenere i dettagli del prodotto)
function getCarrelloItems($user_id) {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantita, c.data_aggiunta, 
               p.id as prodotto_id, p.nome as prodotto_nome, p.prezzo as prodotto_prezzo, 
               p.immagine_principale, p.descrizione_breve
        FROM carrello c 
        JOIN prodotti p ON c.prodotto_id = p.id 
        WHERE c.utente_id = ? 
        ORDER BY c.data_aggiunta DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funzione per svuotare il carrello
function svuotaCarrello($user_id) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    $stmt = $pdo->prepare("DELETE FROM carrello WHERE utente_id = ?");
    return $stmt->execute([$user_id]);
}

// Funzione per verificare se un prodotto è nel carrello
function isProdottoInCarrello($user_id, $prodotto_id) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrello WHERE utente_id = ? AND prodotto_id = ?");
    $stmt->execute([$user_id, $prodotto_id]);
    return $stmt->fetchColumn() > 0;
}
?>
