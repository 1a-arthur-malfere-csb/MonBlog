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
            // Vérifier si l'email est déjà pris
            $req = $bdd->prepare(
                "SELECT * FROM T_UTILISATEUR WHERE UTI_EMAIL = ?",
            );
            $req->execute([$email]);
            $user = $req->fetch();

            if ($user) {
                $erreur = "Cet email est déjà utilisé.";
            } else {
                // Hasher le mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insérer le nouvel utilisateur
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
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css" />
        <title>Mon Blog - Inscription</title>
    </head>
    <body>
        <div id="global">
            <header>
                <a href="index.php"><h1 id="titreBlog">Mon Blog</h1></a>
                <p>Je vous souhaite la bienvenue sur ce modeste blog.</p>
            </header>
            <div id="contenu">
                <article>
                    <header>
                        <h1 class="titreBillet">Inscription</h1>
                    </header>
                    <form method="post" action="register.php">
                        <p>
                            <label for="email">Email</label><br />
                            <input type="email" name="email" id="email" />
                        </p>
                        <p>
                            <label for="password">Mot de passe</label><br />
                            <input type="password" name="password" id="password" />
                        </p>
                        <p>
                            <input type="submit" value="S'inscrire" />
                        </p>
                    </form>
                    <?php if (isset($erreur)): ?>
                        <p class="erreur"><?= $erreur ?></p>
                    <?php endif; ?>
                </article>
            </div> <!-- #contenu -->
            <footer id="piedBlog">
                Blog réalisé avec PHP, HTML5 et CSS.
            </footer>
        </div> <!-- #global -->
    </body>
</html>
