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
    // Nettoyage et validation de l'email
    $email = cleanInput($_POST['mail']);
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }

    // Validation du mot de passe
    $password = $_POST['mdp'];
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    }

    // Si pas d'erreurs, procéder à la connexion
    if (empty($errors)) {
        try {
            $db = Database::connect();
            
            // Vérifier les identifiants
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Créer la session utilisateur
                $_SESSION['user_mail'] = $email;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];

                // Transférer le panier temporaire vers le compte utilisateur
                if (isset($_COOKIE['userTemp'])) {
                    $userTemp = $_COOKIE['userTemp'];
                    
                    // Mettre à jour les articles du panier temporaire
                    $updateQuery = "UPDATE panier SET userTemp = :userId WHERE userTemp = :userTemp";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->bindParam(':userId', $user['id']);
                    $updateStmt->bindParam(':userTemp', $userTemp);
                    $updateStmt->execute();
                }

                $registered = "Vous êtes connecté";
                header('Location: ' . $_SERVER['HTTP_REFERER'] . '?registered=' . $registered);
                exit();
            } else {
                $incorrect = "Email ou mot de passe incorrect";
                header('Location: inscription.php?incorrect=' . $incorrect);
                exit();
            }
        } catch (PDOException $e) {
            $incorrect = "Erreur de connexion à la base de données";
            header('Location: inscription.php?incorrect=' . $incorrect);
            exit();
        }
        
        Database::disconnect();
    } else {
        $incorrect = implode(", ", $errors);
        header('Location: inscription.php?incorrect=' . $incorrect);
        exit();
    }
}
?>
