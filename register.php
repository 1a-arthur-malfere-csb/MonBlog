<?php
session_start();

$bdd = new PDO(
    "mysql:host=localhost;dbname=monblog;charset=utf8",
    "userblog",
    "password",
);

if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    if (!empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "Format d'email invalide.";
        } else {
            $req = $bdd->prepare(
                "SELECT * FROM T_UTILISATEUR WHERE UTI_EMAIL = ?",
            );
            $req->execute([$email]);
            $user = $req->fetch();

            if ($user) {
                $erreur = "Cet email est déjà utilisé.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $req = $bdd->prepare(
                    "INSERT INTO T_UTILISATEUR(UTI_EMAIL, UTI_PASSWORD) VALUES (?, ?)",
                );
                $req->execute([$email, $password_hash]);

                header("Location: login.php");
            }
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <title>Mon Blog - Inscription</title>
</head>

<body>
    <div class="form-container">
        <div class="logo-placeholder">Mon Blog</div>
        <h1 class="subtitle">Créer votre compte Mon Blog</h1>

        <form method="post" action="register.php">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required />

            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required />

            <div class="form-actions">
                <a href="login.php" class="bouton bouton--secondary">Se connecter</a>
                <input type="submit" value="Suivant" class="bouton" />
            </div>
        </form>

        <?php if (isset($erreur)): ?>
            <p class="error-message"><?= $erreur ?></p>
        <?php endif; ?>
    </div>
    <footer id="piedBlog">
        Blog réalisé avec PHP, HTML5 et CSS.
    </footer>
</body>

</html>
