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

        if (isset($_FILES['image']) && $_FILES['image']['size'] <= 1024 * 1024) {

            $extention = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowedExt = ['jpg', 'png', 'jpeg'];

            if (in_array($extention, $allowedExt)) {

                $newName = uniqid('img') . '.' . $extention;
                move_uploaded_file($_FILES['image']['tmp_name'], '../images/' . $newName);
                $filedUpdate[] = "image = :image";
                $valueToBind[":image"] = $newName;

                if ($product['image'] && file_exists("../images/" . $product['image'])) {
                    unlink("../images/" . $product['image']);
                }
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
