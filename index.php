<?php session_start(); ?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <title>Mon Blog</title>
</head>

<body>
    <header class="main-header">
        <a href="index.php">
            <h1 id="titreBlog">Mon Blog</h1>
        </a>

        <form action="search.php" method="get" class="search-form">
            <input type="search" name="q" placeholder="Rechercher des articles..." aria-label="Rechercher des articles" value="<?= isset(
                $_GET["q"],
            )
                ? htmlspecialchars($_GET["q"])
                : "" ?>">
            <button type="submit" aria-label="Rechercher">
                <svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                </svg>
            </button>
        </form>

        <div id="user-menu">
            <?php if (isset($_SESSION["user_id"])): ?>
                <div class="user-info">
                    <div class="avatar">
                        <span><?= htmlspecialchars(
                            strtoupper(substr($_SESSION["user_email"], 0, 1)),
                        ) ?></span>
                    </div>
                    <p><?= htmlspecialchars($_SESSION["user_email"]) ?></p>
                </div>
                <a href="logout.php" class="bouton">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="bouton">Connexion</a>
                <a href="register.php" class="bouton">Inscription</a>
            <?php endif; ?>
        </div>
    </header>
    <div id="global">
        <div id="contenu">
            <?php
            $bdd = new PDO(
                "mysql:host=localhost;dbname=monblog;charset=utf8",
                "userblog",
                "password",
            );
            $billets = $bdd->query(
                "SELECT BIL_ID as id, BIL_DATE as date, BIL_TITRE as titre, BIL_CONTENU as contenu FROM T_BILLET ORDER BY BIL_ID DESC",
            );

            foreach ($billets as $billet): ?>
                <article id="post-<?= $billet["id"] ?>">
                    <header>
                        <h1 class="titreBillet"><?= htmlspecialchars(
                            $billet["titre"],
                        ) ?></h1>
                        <time><?= $billet["date"] ?></time>
                    </header>
                    <p><?= nl2br(htmlspecialchars($billet["contenu"])) ?></p>
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <a href="commenter.php?id=<?= $billet[
                            "id"
                        ] ?>" class="bouton">Ajouter un commentaire</a>
                    <?php endif; ?>

                    <div class="commentaires">
                        <h2>Commentaires</h2>
                        <?php
                        $reqCommentaires = $bdd->prepare(
                            "SELECT c.COM_ID, c.COM_DATE, c.COM_CONTENU, c.COM_MODIFIED, c.COM_MODIFIED_DATE, u.UTI_ID, u.UTI_EMAIL
                                 FROM T_COMMENTAIRE c
                                 JOIN T_UTILISATEUR u ON c.UTI_ID = u.UTI_ID
                                 WHERE c.BIL_ID = ?
                                 ORDER BY c.COM_DATE",
                        );
                        $reqCommentaires->execute([$billet["id"]]);
                        $commentaires = $reqCommentaires->fetchAll();

                        if (empty($commentaires)): ?>
                            <p>Aucun commentaire pour le moment.</p>
                        <?php else:foreach ($commentaires as $commentaire): ?>
                                <div class="commentaire" data-comment-id="<?= $commentaire[
                                    "COM_ID"
                                ] ?>">
                                    <p class="commentaireAuteur">
                                        <strong><?= htmlspecialchars(
                                            $commentaire["UTI_EMAIL"],
                                        ) ?></strong> -
                                        <time><?= $commentaire[
                                            "COM_DATE"
                                        ] ?></time>
                                        <?php if (
                                            $commentaire["COM_MODIFIED"]
                                        ): ?>
                                            <span class="modified-notice">(modifié le <time><?= $commentaire[
                                                "COM_MODIFIED_DATE"
                                            ] ?></time>)</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="commentaireContenu"><?= nl2br(
                                        htmlspecialchars(
                                            $commentaire["COM_CONTENU"],
                                        ),
                                    ) ?></p>
                                </div>
                            <?php endforeach;endif;
                        ?>
                    </div>
                </article>
            <?php endforeach;
            ?>
        </div>
        <footer id="piedBlog">
            Blog réalisé avec PHP, HTML5 et CSS.
        </footer>
    </div>

    <div id="context-menu">
        <ul>
            <li id="edit-comment">Modifier</li>
            <li id="delete-comment">Supprimer</li>
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contextMenu = document.getElementById('context-menu');
            let currentCommentId = null;

            document.querySelectorAll('.commentaire').forEach(comment => {
                comment.addEventListener('contextmenu', function (e) {
                    e.preventDefault();
                    currentCommentId = this.dataset.commentId;

                    contextMenu.style.display = 'block';
                    contextMenu.style.left = e.pageX + 'px';
                    contextMenu.style.top = e.pageY + 'px';
                });
            });

            document.addEventListener('click', function (e) {
                if (e.target.offsetParent != contextMenu) {
                    contextMenu.style.display = 'none';
                    currentCommentId = null;
                }
            });

            document.getElementById('delete-comment').addEventListener('click', function () {
                if (!currentCommentId) return;

                if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
                    const formData = new FormData();
                    formData.append('comment_id', currentCommentId);

                    fetch('delete_comment.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erreur : ' + data.message);
                            }
                        })
                        .catch(error => console.error('Erreur:', error));
                }
                contextMenu.style.display = 'none';
            });

            document.getElementById('edit-comment').addEventListener('click', function () {
                if (!currentCommentId) return;

                const commentIdForEdit = currentCommentId;
                const commentDiv = document.querySelector(`.commentaire[data-comment-id='${commentIdForEdit}']`);
                const contentP = commentDiv.querySelector('.commentaireContenu');
                const originalContent = contentP.innerText;

                const textarea = document.createElement('textarea');
                textarea.value = originalContent;

                const saveButton = document.createElement('button');
                saveButton.innerText = 'Sauvegarder';
                saveButton.className = 'bouton';

                contentP.innerHTML = '';
                contentP.appendChild(textarea);
                contentP.appendChild(saveButton);

                textarea.focus();

                saveButton.addEventListener('click', function () {
                    const newContent = textarea.value.trim();
                    if (newContent === '') {
                        alert('Le commentaire ne peut pas être vide.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('comment_id', commentIdForEdit);
                    formData.append('content', newContent);

                    fetch('edit_comment.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erreur : ' + data.message);
                                contentP.innerText = originalContent;
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            contentP.innerText = originalContent;
                        });
                });

                contextMenu.style.display = 'none';
            });
        });
    </script>
</body>

</html>
