<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css" />
        <title>Mon Blog</title>
    </head>
    <body>
        <div id="global">
            <header>
                <a href="index.php"><h1 id="titreBlog">Mon Blog</h1></a>
                <p>Je vous souhaite la bienvenue sur ce modeste blog.</p>
            </header>
            <div id="contenu">
                <?php
                $bdd = new PDO(
                    "mysql:host=localhost;dbname=monblog;charset=utf8",
                    "userblog",
                    "password",
                );
                $billets = $bdd->query(
                    "select BIL_ID as id, BIL_DATE as date," .
                        " BIL_TITRE as titre, BIL_CONTENU as contenu from T_BILLET" .
                        " order by BIL_ID desc",
                );
                foreach ($billets as $billet): ?>
                    <article>
                        <header>
                            <h1 class="titreBillet"><?= $billet["titre"] ?></h1>
                            <time><?= $billet["date"] ?></time>
                        </header>
                        <p><?= $billet["contenu"] ?></p>
                        <a href="commenter.php?id=<?= $billet[
                            "id"
                        ] ?>" class="bouton">Ajouter un commentaire</a>

                        <div class="commentaires">
                            <h2>Commentaires</h2>
                            <?php
                            $reqCommentaires = $bdd->prepare(
                                "select COM_ID as id, COM_DATE as date," .
                                    " COM_AUTEUR as auteur, COM_CONTENU as contenu from T_COMMENTAIRE" .
                                    " where BIL_ID = ? order by COM_DATE",
                            );
                            $reqCommentaires->execute([$billet["id"]]);
                            $commentaires = $reqCommentaires->fetchAll();

                            if (empty($commentaires)): ?>
                                <p class="aucunCommentaire">Aucun commentaire pour le moment.</p>
                            <?php else:foreach (
                                    $commentaires
                                    as $commentaire
                                ): ?>
                                    <div class="commentaire">
                                        <p class="commentaireAuteur"><strong><?= htmlspecialchars(
                                            $commentaire["auteur"],
                                        ) ?></strong> - <time><?= $commentaire[
    "date"
] ?></time></p>
                                        <p class="commentaireContenu"><?= htmlspecialchars(
                                            $commentaire["contenu"],
                                        ) ?></p>
                                    </div>
                                <?php endforeach;endif;
                            ?>
                        </div>

                    </article>
                    <hr />
                <?php endforeach;
                ?>
            </div> <!-- #contenu -->
            <footer id="piedBlog">
                Blog réalisé avec PHP, HTML5 et CSS.
            </footer>
        </div> <!-- #global -->
    </body>
</html>
