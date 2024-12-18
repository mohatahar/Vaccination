<?php
session_start();
require_once 'db.php'; // Assurez-vous d'avoir un fichier de configuration avec la connexion à la base de données

// Vérifier si l'utilisateur est connecté (ajoutez votre logique de connexion)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer le numéro de lot à supprimer
$lot_number = isset($_GET['lot_number']) ? $_GET['lot_number'] : null;

if (!$lot_number) {
    $_SESSION['error_message'] = "Numéro de lot non spécifié.";
    header('Location: vaccin.php');
    exit();
}

// Préparer la requête de suppression
$stmt = $conn->prepare("DELETE FROM entrees_stock WHERE lot_number = ?");
$stmt->bind_param("s", $lot_number);

// Exécuter la suppression
if ($stmt->execute()) {
    // Vérifier si une ligne a été supprimée
    if ($stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "Stock supprimé avec succès.";
    } else {
        $_SESSION['error_message'] = "Aucun stock trouvé avec ce numéro de lot.";
    }
} else {
    $_SESSION['error_message'] = "Erreur lors de la suppression : " . $stmt->error;
}

// Rediriger vers la page de stock
header('Location: vaccin.php');
exit();
?>