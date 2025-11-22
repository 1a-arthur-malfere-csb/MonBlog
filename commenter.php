<?php
session_start();

if (!isset($_SESSION["user_email"])) {
    header("Location: login.php");
    exit();
}

$bdd = new PDO(
    "mysql:host=localhost;dbname=monblog;charset=utf8",
    "userblog",
    "password",
);

if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $id_billet = $_GET["id"];
} else {
    header("Location: index.php");
}

if (isset($_POST["commentaire"])) {
    $commentaire = $_POST["commentaire"];
    $user_id = $_SESSION["user_id"];

    if (!empty($commentaire)) {
        $req = $bdd->prepare(
            "INSERT INTO T_COMMENTAIRE(COM_DATE, COM_CONTENU, BIL_ID, UTI_ID) VALUES (NOW(), ?, ?, ?)",
        );
        $req->execute([$commentaire, $id_billet, $user_id]);
        header("Location: index.php");
    } else {
        $erreur = "Veuillez remplir le champ commentaire.";
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <title>Mon Blog - Ajouter un commentaire</title>
</head>

<body>
    <header class="main-header">
        <a href="index.php">
            <h1 id="titreBlog">Mon Blog</h1>
        </a>
        <div id="user-menu">
            <p><?= htmlspecialchars($_SESSION["user_email"]) ?></p>
            <a href="logout.php" class="bouton">Déconnexion</a>
        </div>
    </header>
    <div id="global">
        <div id="contenu">
            <article>
                <header>
                    <h1 class="titreBillet">Ajouter un commentaire</h1>
                </header>
                <form method="post" action="commenter.php?id=<?= $id_billet ?>">
                    <p>
                        <label for="commentaire">Commentaire</label><br />
                        <textarea name="commentaire" id="commentaire" rows="5" cols="50"></textarea>
                    </p>
                    <p>
                        <input type="submit" value="Ajouter le commentaire" />
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