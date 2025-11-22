<?php
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Une erreur est survenue.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Utilisateur non connecté.';
    echo json_encode($response);
    exit();
}

if (isset($_POST['comment_id'])) {
    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];

    $bdd = new PDO("mysql:host=localhost;dbname=monblog;charset=utf8", "userblog", "password");

    // Vérifier que l'utilisateur est bien l'auteur du commentaire
    $req = $bdd->prepare("SELECT UTI_ID FROM T_COMMENTAIRE WHERE COM_ID = ?");
    $req->execute([$comment_id]);
    $comment = $req->fetch();

    if ($comment && $comment['UTI_ID'] == $user_id) {
        // Supprimer le commentaire
        $deleteReq = $bdd->prepare("DELETE FROM T_COMMENTAIRE WHERE COM_ID = ?");
        $deleteResult = $deleteReq->execute([$comment_id]);

        if ($deleteResult) {
            $response['success'] = true;
            $response['message'] = 'Commentaire supprimé avec succès.';
        } else {
            $response['message'] = 'Erreur lors de la suppression du commentaire.';
        }
    } else {
        $response['message'] = 'Vous n\'êtes pas autorisé à supprimer ce commentaire.';
    }
} else {
    $response['message'] = 'Données manquantes.';
}

echo json_encode($response);
