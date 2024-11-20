<?php
require 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user = $_SESSION['user_id'];
} else {
    $user = $_COOKIE['userTemp'];
}

try {
    $db = Database::connect();
    
    // Récupérer les informations du produit
    $productId = filter_input(INPUT_GET, 'id_item', FILTER_VALIDATE_INT);
    $prix = filter_input(INPUT_GET, 'prix', FILTER_VALIDATE_FLOAT);
    
    if (!$productId || !$prix) {
        throw new Exception("Données du produit invalides");
    }
    
    // Vérifier si le produit existe déjà dans le panier
    $query = "SELECT id, qte FROM panier WHERE id_item = :id_item AND userTemp = :userTemp";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id_item' => $productId,
        ':userTemp' => $user
    ]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Mettre à jour la quantité
        $query = "UPDATE panier SET qte = qte + 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $existingItem['id']]);
        
        // Récupérer les personnalisations si présentes
        if (isset($_POST['sauce_id']) || isset($_POST['fromage_id']) || isset($_POST['legumes_ids'])) {
            $sauce_id = filter_input(INPUT_POST, 'sauce_id', FILTER_VALIDATE_INT);
            $fromage_id = filter_input(INPUT_POST, 'fromage_id', FILTER_VALIDATE_INT);
            $legumes_ids = isset($_POST['legumes_ids']) ? $_POST['legumes_ids'] : null;
            
            // Calculer le prix des suppléments
            require_once 'personnalisation.php';
            $prix_supplements = calculerPrixSupplements($sauce_id, $fromage_id, $legumes_ids);
            
            // Ajouter la personnalisation
            $query = "INSERT INTO personnalisations_panier (panier_id, sauce_id, fromage_id, legumes_ids, prix_total_supplements) 
                     VALUES (:panier_id, :sauce_id, :fromage_id, :legumes_ids, :prix_supplements)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'panier_id' => $existingItem['id'],
                'sauce_id' => $sauce_id,
                'fromage_id' => $fromage_id,
                'legumes_ids' => $legumes_ids ? implode(',', $legumes_ids) : null,
                'prix_supplements' => $prix_supplements
            ]);
            
            // Mettre à jour le prix total
            $query = "UPDATE panier SET prix_total = prix_total + :prix_supplements WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'prix_supplements' => $prix_supplements,
                'id' => $existingItem['id']
            ]);
        }
    } else {
        // Ajouter un nouveau produit
        $query = "INSERT INTO panier (id_item, qte, userTemp, prix_total) VALUES (:id_item, 1, :userTemp, :prix)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':id_item' => $productId,
            ':userTemp' => $user,
            ':prix' => $prix
        ]);
        
        $panier_id = $db->lastInsertId();
        
        // Ajouter les personnalisations si présentes
        if (isset($_POST['sauce_id']) || isset($_POST['fromage_id']) || isset($_POST['legumes_ids'])) {
            $sauce_id = filter_input(INPUT_POST, 'sauce_id', FILTER_VALIDATE_INT);
            $fromage_id = filter_input(INPUT_POST, 'fromage_id', FILTER_VALIDATE_INT);
            $legumes_ids = isset($_POST['legumes_ids']) ? $_POST['legumes_ids'] : null;
            
            // Calculer le prix des suppléments
            require_once 'personnalisation.php';
            $prix_supplements = calculerPrixSupplements($sauce_id, $fromage_id, $legumes_ids);
            
            // Ajouter la personnalisation
            $query = "INSERT INTO personnalisations_panier (panier_id, sauce_id, fromage_id, legumes_ids, prix_total_supplements) 
                     VALUES (:panier_id, :sauce_id, :fromage_id, :legumes_ids, :prix_supplements)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'panier_id' => $panier_id,
                'sauce_id' => $sauce_id,
                'fromage_id' => $fromage_id,
                'legumes_ids' => $legumes_ids ? implode(',', $legumes_ids) : null,
                'prix_supplements' => $prix_supplements
            ]);
            
            // Mettre à jour le prix total
            $query = "UPDATE panier SET prix_total = prix_total + :prix_supplements WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'prix_supplements' => $prix_supplements,
                'id' => $panier_id
            ]);
        }
    }
    
    Database::disconnect();
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // Réponse AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        // Redirection normale
        header('Location: panier.php');
    }
    
} catch (Exception $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        header('Location: index.php?error=' . urlencode($e->getMessage()));
    }
}
?>
