<?php
require '../db.php';
// require 'verifRole.php';

$db = DataBase::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_POST['category'])) {

        $nom = htmlspecialchars($_POST['name']);
        $description = htmlspecialchars($_POST['description']);
        $price = htmlspecialchars($_POST['price']);
        $category = htmlspecialchars($_POST['category']);
        $id = (int)$_POST['id'];

        $filedUpdate = [];
        $valueToBind = [':id' => $id];

        $query = "SELECT * FROM items WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        foreach ($_POST as $cle => $valeur) {
            $value = ($cle == 'price') ? (float)trim($valeur) : (($cle == 'category') ? (int)trim($valeur) : trim($valeur));
            $key = trim($cle);
            if ($value != $product[$key]) {
                $filedUpdate[] = "$key = :$key";
                $valueToBind[":$key"] = $value;
            }
        }

        if (!empty($filedUpdate)) {
            $query2 =
                "UPDATE items
                    SET " . implode(',', $filedUpdate) . "
                    WHERE id = :id";
            $stmt = $db->prepare($query2);
            $stmt->execute($valueToBind);
        }

        header('Location: index.php');
    }
}
