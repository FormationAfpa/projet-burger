<?php
require 'db.php';
session_start();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $db = Database::connect();
        
        $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        
        if (!$productId || !$quantity || $quantity < 1) {
            throw new Exception('Données invalides');
        }

        // Mettre à jour la quantité
        $query = "UPDATE panier SET qte = :quantity WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $success = $stmt->execute();

        if ($success) {
            // Récupérer le prix unitaire et calculer le nouveau total
            $query = "SELECT p.*, i.price FROM panier p JOIN items i ON p.id_item = i.id WHERE p.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculer le nouveau total du produit
            $productTotal = number_format($product['price'] * $quantity, 2, ',', ' ');

            // Calculer le nouveau total du panier
            $query = "SELECT SUM(p.qte * i.price) as total FROM panier p JOIN items i ON p.id_item = i.id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $cartTotal = number_format($result['total'], 2, ',', ' ');

            echo json_encode([
                'success' => true,
                'productTotal' => $productTotal,
                'cartTotal' => $cartTotal
            ]);
        } else {
            throw new Exception('Erreur lors de la mise à jour de la quantité');
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

Database::disconnect();
?>
