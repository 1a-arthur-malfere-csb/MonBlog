<?php session_start(); ?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css" />
    <title>Résultats de recherche - Mon Blog</title>
</head>

<body>
<?php function getRandomColorForUser($email)
{
    $hash = crc32($email);
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
            $query = isset($_GET["q"]) ? trim($_GET["q"]) : "";

            if (!empty($query)) {
                echo "<h1 class='text-center' style='margin-bottom: 20px;'>Résultats pour : " .
                    htmlspecialchars($query) .
                    "</h1>";

                try {
                    $bdd = new PDO(
                        "mysql:host=localhost;dbname=monblog;charset=utf8",
                        "userblog",
                        "password",
                    );
                    $bdd->setAttribute(
                        PDO::ATTR_ERRMODE,
                        PDO::ERRMODE_EXCEPTION,
                    );

                    $searchTerm = "%" . $query . "%";
                    $req = $bdd->prepare(
                        "SELECT BIL_ID as id, BIL_DATE as date, BIL_TITRE as titre, BIL_CONTENU as contenu
                         FROM T_BILLET
                         WHERE BIL_TITRE LIKE ? OR BIL_CONTENU LIKE ?
                         ORDER BY BIL_ID DESC",
                    );
                    $req->execute([$searchTerm, $searchTerm]);
                    $billets = $req->fetchAll();

                    if ($billets) {
                        foreach ($billets as $billet): ?>
                            <article>
                                <header>
                                    <h1 class="titreBillet"><a href="index.php#post-<?= $billet[
                                        "id"
                                    ] ?>"><?= htmlspecialchars(
    $billet["titre"],
) ?></a></h1>
                                    <time><?= $billet["date"] ?></time>
                                </header>
                                <p><?= nl2br(
                                    htmlspecialchars(
                                        substr($billet["contenu"], 0, 200),
                                    ),
                                ) ?>...</p>
                                <a href="index.php#post-<?= $billet[
                                    "id"
                                ] ?>" class="bouton" style="margin-top: 10px;">Lire la suite</a>
                            </article>
                            <hr />
                        <?php endforeach;
                    } else {
                        echo "<p class='text-center' style='font-size: 16px; margin-top: 20px;'>Aucun article trouvé correspondant à votre recherche.</p>";
                    }
                } catch (Exception $e) {
                    echo '<p style="color: red; text-align: center;">Une erreur est survenue lors de la connexion à la base de données.</p>';
                }
            } else {
                if (isset($_GET["q"])) {
                    echo "<h1 class='text-center'>Veuillez entrer un terme de recherche.</h1>";
                }
            }
            ?>
        </div>
        <footer id="piedBlog">
            Blog réalisé avec PHP, HTML5 et CSS.
        </footer>
    </div>
</body>
</html>
