<?php
require_once 'db.php';

/**
 * Obtient les recommandations de produits basées sur un produit spécifique
 * @param int $productId ID du produit actuel
 * @param int $limit Nombre de recommandations à retourner
 * @return array Liste des produits recommandés
 */
function getRecommandations($productId, $limit = 3) {
    $db = Database::connect();
    
    try {
        // Récupérer la catégorie du produit actuel
        $query = "SELECT category FROM items WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $productId]);
        $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentProduct) {
            return [];
        }
        
        // Récupérer les produits de la même catégorie, excluant le produit actuel
        $query = "SELECT i.*, c.name as category_name 
                 FROM items i 
                 JOIN categories c ON i.category = c.id 
                 WHERE i.category = :category 
                 AND i.id != :id 
                 ORDER BY RAND() 
                 LIMIT :limit";
                 
        $stmt = $db->prepare($query);
        $stmt->bindValue(':category', $currentProduct['category'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si on n'a pas assez de recommandations, ajouter des produits populaires d'autres catégories
        if (count($recommendations) < $limit) {
            $remaining = $limit - count($recommendations);
            $excludeIds = array_merge([$productId], array_column($recommendations, 'id'));
            
            $placeholders = str_repeat('?,', count($excludeIds) - 1) . '?';
            $query = "SELECT i.*, c.name as category_name 
                     FROM items i 
                     JOIN categories c ON i.category = c.id 
                     WHERE i.id NOT IN ($placeholders)
                     ORDER BY RAND() 
                     LIMIT ?";
                     
            $stmt = $db->prepare($query);
            $params = array_merge($excludeIds, [$remaining]);
            $stmt->execute($params);
            
            $recommendations = array_merge($recommendations, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        return $recommendations;
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des recommandations : " . $e->getMessage());
        return [];
    } finally {
        Database::disconnect();
    }
}

/**
 * Fonction pour obtenir les recommandations au format JSON (pour les appels AJAX)
 */
if (isset($_GET['product_id'])) {
    header('Content-Type: application/json');
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
    
    if ($productId) {
        $recommendations = getRecommandations($productId);
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ID de produit invalide'
        ]);
    }
    exit;
}
?>
