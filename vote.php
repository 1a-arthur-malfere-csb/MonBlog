<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
  echo json_encode([
    "success" => false,
    "message" => "Vous devez être connecté pour voter",
  ]);
  exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
  exit();
}

$comment_id = filter_input(INPUT_POST, "comment_id", FILTER_VALIDATE_INT);
$vote_type = filter_input(INPUT_POST, "vote_type", FILTER_SANITIZE_STRING);
$user_id = $_SESSION["user_id"];

if (!$comment_id || !in_array($vote_type, ["up", "down"])) {
  echo json_encode(["success" => false, "message" => "Données invalides"]);
  exit();
}

try {
  $bdd = new PDO(
    "mysql:host=localhost;dbname=monblog;charset=utf8",
    "userblog",
    "password",
  );
  $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $checkComment = $bdd->prepare(
    "SELECT COM_ID FROM T_COMMENTAIRE WHERE COM_ID = ?",
  );
  $checkComment->execute([$comment_id]);
  if (!$checkComment->fetch()) {
    echo json_encode([
      "success" => false,
      "message" => "Commentaire introuvable",
    ]);
    exit();
  }

  $existingVote = $bdd->prepare(
    "SELECT VOT_TYPE FROM T_VOTE WHERE COM_ID = ? AND UTI_ID = ?",
  );
  $existingVote->execute([$comment_id, $user_id]);
  $currentVote = $existingVote->fetch();

  if ($currentVote) {
    if ($currentVote["VOT_TYPE"] === $vote_type) {
      $deleteVote = $bdd->prepare(
        "DELETE FROM T_VOTE WHERE COM_ID = ? AND UTI_ID = ?",
      );
      $deleteVote->execute([$comment_id, $user_id]);
      $action = "removed";
    } else {
      $updateVote = $bdd->prepare(
        "UPDATE T_VOTE SET VOT_TYPE = ?, VOT_DATE = CURRENT_TIMESTAMP WHERE COM_ID = ? AND UTI_ID = ?",
      );
      $updateVote->execute([$vote_type, $comment_id, $user_id]);
      $action = "updated";
    }
  } else {
    $insertVote = $bdd->prepare(
      "INSERT INTO T_VOTE (COM_ID, UTI_ID, VOT_TYPE) VALUES (?, ?, ?)",
    );
    $insertVote->execute([$comment_id, $user_id, $vote_type]);
    $action = "added";
  }

  $getVoteCounts = $bdd->prepare("
        SELECT
            SUM(CASE WHEN VOT_TYPE = 'up' THEN 1 ELSE 0 END) as upvotes,
            SUM(CASE WHEN VOT_TYPE = 'down' THEN 1 ELSE 0 END) as downvotes
        FROM T_VOTE
        WHERE COM_ID = ?
    ");
  $getVoteCounts->execute([$comment_id]);
  $votes = $getVoteCounts->fetch();

  $upvotes = (int) $votes["upvotes"];
  $downvotes = (int) $votes["downvotes"];
  $total_score = $upvotes - $downvotes;

  $getCurrentVote = $bdd->prepare(
    "SELECT VOT_TYPE FROM T_VOTE WHERE COM_ID = ? AND UTI_ID = ?",
  );
  $getCurrentVote->execute([$comment_id, $user_id]);
  $userCurrentVote = $getCurrentVote->fetch();
  $user_vote = $userCurrentVote ? $userCurrentVote["VOT_TYPE"] : null;

  echo json_encode([
    "success" => true,
    "action" => $action,
    "upvotes" => $upvotes,
    "downvotes" => $downvotes,
    "score" => $total_score,
    "user_vote" => $user_vote,
  ]);
} catch (PDOException $e) {
  error_log("Erreur de vote : " . $e->getMessage());
  echo json_encode([
    "success" => false,
    "message" => "Erreur interne du serveur",
  ]);
}
