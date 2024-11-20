<?php
require 'db.php';
session_start();

// Fonction pour nettoyer les entrées utilisateur
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validation des entrées
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = Database::connect();

    // Validation du nom
    $nom = cleanInput($_POST['nom']);
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    } elseif (strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères";
    }

    // Validation de l'email
    $email = cleanInput($_POST['email']);
    if (empty($email)) {
        $errors[] = "L'adresse email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'adresse email invalide";
    } else {
        // Vérification si l'email existe déjà
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $errors[] = "Cette adresse email est déjà utilisée";
        }
    }

    // Validation du mot de passe
    $password = $_POST['password'];
    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    if (empty($errors)) {
        try {
            // Hachage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertion de l'utilisateur
            $query = "INSERT INTO users (nom, email, password, role) VALUES (:nom, :email, :password, :role)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', 'user');
            $stmt->execute();

            // Récupération de l'ID de l'utilisateur nouvellement créé
            $userId = $db->lastInsertId();

            // Transfert du panier temporaire vers le compte utilisateur
            if (isset($_COOKIE['userTemp'])) {
                $query = "UPDATE panier SET userTemp = :userId WHERE userTemp = :tempId";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':userId', $userId);
                $stmt->bindParam(':tempId', $_COOKIE['userTemp']);
                $stmt->execute();
            }

            $_SESSION['user_id'] = $userId;
            header('Location: inscription.php?registered=' . urlencode("Inscription réussie ! Vous êtes maintenant connecté."));
            exit();

        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        header('Location: inscription.php?incorrect=' . urlencode(implode(", ", $errors)));
        exit();
    }

    Database::disconnect();
}
?>
