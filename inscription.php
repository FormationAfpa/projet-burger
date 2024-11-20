<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription et Connexion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css">

    <style>
        .container-account {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .vertical-line {
            border-right: 1px solid black;
            margin-right: 50px
        }
    </style>
</head>

<body>

    <?php if (isset($_GET["incorrect"])) { ?>
        <div class="alert alert-danger" role="alert" style="text-align:center">
            <?= $_GET["incorrect"] ?>
        </div>
    <?php } ?>


    <?php if (isset($_GET["registered"])) { ?>
        <div class="alert alert-success" role="alert" style="text-align:center">
            <?= $_GET["registered"] ?>
        </div>
    <?php } ?>


    <div class="container-account">
        <div class="row justify-content-center w-100">
            <div class="col-md-5 vertical-line">
                <h2 class="text-center mb-4">Inscription</h2>
                <form action="inscriptionREQ.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse Email</label>
                        <input type="email" class="form-control" id="email" name="mail" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="mdp" required>
                        <small class="text-muted">Le mot de passe doit contenir au moins 8 caractères</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                </form>
            </div>

            <div class="col-md-5">
                <h2 class="text-center mb-4">Connexion</h2>
                <form action="connexionREQ.php" method="post">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Adresse Email</label>
                        <input type="email" class="form-control" id="loginEmail" name="mail" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="loginPassword" name="mdp" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>