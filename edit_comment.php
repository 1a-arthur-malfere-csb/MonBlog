<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Une erreur est survenue.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Utilisateur non connecté.';
    echo json_encode($response);
    exit();
}

if (isset($_POST['comment_id']) && isset($_POST['content'])) {
    $comment_id = intval($_POST['comment_id']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (empty($content)) {
        $response['message'] = 'Le commentaire ne peut pas être vide.';
    } else {
        $bdd = new PDO("mysql:host=localhost;dbname=monblog;charset=utf8", "userblog", "password");

        // Vérifier que l'utilisateur est bien l'auteur du commentaire
        $req = $bdd->prepare("SELECT UTI_ID FROM T_COMMENTAIRE WHERE COM_ID = ?");
        $req->execute([$comment_id]);
        $comment = $req->fetch();

        if ($comment && $comment['UTI_ID'] == $user_id) {
            // Mettre à jour le commentaire
            $updateReq = $bdd->prepare(
                "UPDATE T_COMMENTAIRE SET COM_CONTENU = ?, COM_MODIFIED = TRUE, COM_MODIFIED_DATE = NOW() WHERE COM_ID = ?"
            );
            $updateResult = $updateReq->execute([$content, $comment_id]);

            if ($updateResult) {
                $response['success'] = true;
                $response['message'] = 'Commentaire mis à jour avec succès.';
                $response['new_content'] = htmlspecialchars($content);
                $response['modified_date'] = date('Y-m-d H:i:s');
            } else {
                $response['message'] = 'Erreur lors de la mise à jour du commentaire.';
            }
        } else {
            $response['message'] = 'Vous n\'êtes pas autorisé à modifier ce commentaire.';
        }
    }
} else {
    $response['message'] = 'Données manquantes.';
}

echo json_encode($response);
