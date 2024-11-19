<?php
if ($product['choice'] == 1) {
?>

    <select class="form-control" id="taille<?= $product['id'] ?>" name="taille">

    <?php
    if ($product['category'] == 5) {
        $query3 = "SELECT * FROM boisson";
        $stmt = $db->prepare($query3);
        $stmt->execute();
        $boissons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($boissons as $taille) {
            echo "<option value='" . $taille['taille'] . "'>" . $taille['taille'] . "</option>";
        }
    } else {
        $query3 = "SELECT * FROM choix WHERE id_item = :id";
        $stmt = $db->prepare($query3);
        $stmt->execute([":id" => $product['id']]);
        $choix = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($choix as $gout) {
            echo "<option value='" . $gout['nom_choix'] . "'>" . $gout['nom_choix'] . "</option>";
        }
    }
}
    ?>