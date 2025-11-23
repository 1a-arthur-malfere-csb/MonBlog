<?php session_start(); ?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <title>Mon Blog</title>
</head>

<body>
<?php function getRandomColorForUser($email)
{
    $hash = crc32($email);
    // A list of pleasant, Material Design-like colors. Yellow is excluded for better contrast with white text.
    $colors = [
        "#F44336",
        "#E91E63",
        "#9C27B0",
        "#673AB7",
        "#3F51B5",
        "#2196F3",
        "#03A9F4",
        "#00BCD4",
        "#009688",
        "#4CAF50",
        "#8BC34A",
        "#CDDC39",
        "#FFC107",
        "#FF9800",
        "#FF5722",
        "#795548",
        "#9E9E9E",
        "#607D8B",
    ];
    return $colors[abs($hash) % count($colors)];
} ?>
    <header class="main-header">
        <a href="index.php">
            <h1 id="titreBlog">Mon Blog</h1>
        </a>
        <div id="user-menu">
            <?php if (isset($_SESSION["user_id"])): ?>
                <div class="user-info">
                    <div class="avatar" style="background-color: <?= htmlspecialchars(
                        getRandomColorForUser($_SESSION["user_email"]),
                    ) ?>">
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
                <article>
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
                            <p class="aucunCommentaire">Aucun commentaire pour le moment.</p>
                        <?php else:foreach ($commentaires as $commentaire): ?>
                                <?php $isAuthor =
                                    isset($_SESSION["user_id"]) &&
                                    $_SESSION["user_id"] ==
                                        $commentaire["UTI_ID"]; ?>
                                <div class="commentaire <?= $isAuthor
                                    ? "own-comment"
                                    : "" ?>" data-comment-id="<?= $commentaire[
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
                <hr />
            <?php endforeach;
            ?>
        </div>
        <footer id="piedBlog">
            Blog réalisé avec PHP, HTML5 et CSS.
        </footer>
    </div>

    <!-- Menu contextuel (caché par défaut) -->
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

            // --- Gestion du menu contextuel ---
            document.querySelectorAll('.own-comment').forEach(comment => {
                comment.addEventListener('contextmenu', function (e) {
                    e.preventDefault();
                    currentCommentId = this.dataset.commentId;

                    contextMenu.style.display = 'block';
                    contextMenu.style.left = e.pageX + 'px';
                    contextMenu.style.top = e.pageY + 'px';
                });
            });

            // Cacher le menu si on clique ailleurs
            document.addEventListener('click', function (e) {
                if (e.target.offsetParent != contextMenu) {
                    contextMenu.style.display = 'none';
                    currentCommentId = null;
                }
            });

            // --- Action de suppression ---
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

            // --- Action de modification ---
            document.getElementById('edit-comment').addEventListener('click', function () {
                if (!currentCommentId) return;

                const commentIdForEdit = currentCommentId;
                const commentDiv = document.querySelector(`.commentaire[data-comment-id='${commentIdForEdit}']`);
                const contentP = commentDiv.querySelector('.commentaireContenu');
                const originalContent = contentP.innerText;

                // Remplacer le paragraphe par un textarea
                const textarea = document.createElement('textarea');
                textarea.style.width = '100%';
                textarea.style.height = '80px';
                textarea.value = originalContent;

                const saveButton = document.createElement('button');
                saveButton.innerText = 'Sauvegarder';
                saveButton.className = 'bouton';
                saveButton.style.marginTop = '5px';

                contentP.innerHTML = '';
                contentP.appendChild(textarea);
                contentP.appendChild(saveButton);

                textarea.focus();

                // Action de sauvegarde
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
                                // Restaurer le contenu original en cas d'erreur
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
