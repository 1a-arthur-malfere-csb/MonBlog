<?php
session_start();

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

if (isset($_POST["auteur"]) && isset($_POST["commentaire"])) {
    $auteur = $_POST["auteur"];
    $commentaire = $_POST["commentaire"];

    if (!empty($auteur) && !empty($commentaire)) {
        $req = $bdd->prepare(
            "INSERT INTO T_COMMENTAIRE(COM_DATE, COM_AUTEUR, COM_CONTENU, BIL_ID) VALUES (NOW(), ?, ?, ?)",
        );
        $req->execute([$auteur, $commentaire, $id_billet]);
        header("Location: index.php");
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
        <title>Mon Blog - Ajouter un commentaire</title>
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
                        <h1 class="titreBillet">Ajouter un commentaire</h1>
                    </header>
                    <form method="post" action="commenter.php?id=<?= $id_billet ?>">
                        <p>
                            <label for="auteur">Auteur</label><br />
                            <input type="text" name="auteur" id="auteur" />
                        </p>
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
