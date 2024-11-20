<?php
require 'db.php';
session_start();

// Récupération des options de personnalisation depuis la base de données
function getPersonnalisationOptions() {
    $db = Database::connect();
    
    // Récupérer les sauces disponibles
    $query = "SELECT * FROM sauces WHERE disponible = true ORDER BY prix_supplement ASC";
    $sauces = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les fromages disponibles
    $query = "SELECT * FROM fromages WHERE disponible = true ORDER BY prix_supplement ASC";
    $fromages = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les légumes disponibles
    $query = "SELECT * FROM legumes WHERE disponible = true ORDER BY prix_supplement ASC";
    $legumes = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    Database::disconnect();
    
    return [
        'sauces' => $sauces,
        'fromages' => $fromages,
        'legumes' => $legumes
    ];
}

// Fonction pour calculer le prix total des suppléments
function calculerPrixSupplements($sauce_id, $fromage_id, $legumes_ids) {
    $db = Database::connect();
    $prix_total = 0.00;
    
    // Prix de la sauce
    if ($sauce_id) {
        $query = "SELECT prix_supplement FROM sauces WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $sauce_id]);
        $prix_total += floatval($stmt->fetchColumn());
    }
    
    // Prix du fromage
    if ($fromage_id) {
        $query = "SELECT prix_supplement FROM fromages WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $fromage_id]);
        $prix_total += floatval($stmt->fetchColumn());
    }
    
    // Prix des légumes
    if ($legumes_ids) {
        $legumes_array = explode(',', $legumes_ids);
        $placeholders = str_repeat('?,', count($legumes_array) - 1) . '?';
        $query = "SELECT SUM(prix_supplement) FROM legumes WHERE id IN ($placeholders)";
        $stmt = $db->prepare($query);
        $stmt->execute($legumes_array);
        $prix_total += floatval($stmt->fetchColumn());
    }
    
    Database::disconnect();
    return $prix_total;
}

// Traitement de l'ajout au panier avec personnalisation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => '', 'prix_total' => 0];
    
    try {
        $db = Database::connect();
        
        $panier_id = filter_input(INPUT_POST, 'panier_id', FILTER_VALIDATE_INT);
        $sauce_id = filter_input(INPUT_POST, 'sauce_id', FILTER_VALIDATE_INT);
        $fromage_id = filter_input(INPUT_POST, 'fromage_id', FILTER_VALIDATE_INT);
        $legumes_ids = isset($_POST['legumes_ids']) ? implode(',', $_POST['legumes_ids']) : '';
        
        // Calculer le prix total des suppléments
        $prix_supplements = calculerPrixSupplements($sauce_id, $fromage_id, $legumes_ids);
        
        // Insérer la personnalisation
        $query = "INSERT INTO personnalisations_panier (panier_id, sauce_id, fromage_id, legumes_ids, prix_total_supplements) 
                 VALUES (:panier_id, :sauce_id, :fromage_id, :legumes_ids, :prix_supplements)";
        $stmt = $db->prepare($query);
        $success = $stmt->execute([
            'panier_id' => $panier_id,
            'sauce_id' => $sauce_id,
            'fromage_id' => $fromage_id,
            'legumes_ids' => $legumes_ids,
            'prix_supplements' => $prix_supplements
        ]);
        
        if ($success) {
            // Mettre à jour le prix total dans le panier
            $query = "UPDATE panier SET prix_total = prix_total + :prix_supplements WHERE id = :panier_id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'prix_supplements' => $prix_supplements,
                'panier_id' => $panier_id
            ]);
            
            $response = [
                'success' => true,
                'message' => 'Personnalisation ajoutée avec succès',
                'prix_total' => $prix_supplements
            ];
        }
        
    } catch (PDOException $e) {
        $response['message'] = "Erreur lors de l'ajout de la personnalisation: " . $e->getMessage();
    }
    
    Database::disconnect();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Si c'est une requête GET, retourner les options de personnalisation
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    echo json_encode(getPersonnalisationOptions());
    exit;
}
?>
