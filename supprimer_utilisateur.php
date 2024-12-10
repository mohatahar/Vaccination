<?php
require_once 'db.php';
session_start();

// Implement basic authentication check
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Check if an ID was provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect with an error message if no ID is provided
    $_SESSION['error_message'] = "Aucun utilisateur spécifié pour la suppression.";
    header('Location: gestion_utilisateurs.php');
    exit();
}

$user_id = (int)$_GET['id'];

try {
    // Prevent deleting the current logged-in user
    $current_username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_to_delete = $result->fetch_assoc();

    if ($user_to_delete && $user_to_delete['username'] === $current_username) {
        $_SESSION['error_message'] = "Vous ne pouvez pas supprimer votre propre compte.";
        header('Location: gestion_utilisateurs.php');
        exit();
    }

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $result = $stmt->execute();

    if ($result) {
        // Set success message
        $_SESSION['success_message'] = "Utilisateur supprimé avec succès.";
    } else {
        // Set error message if deletion failed
        $_SESSION['error_message'] = "Impossible de supprimer l'utilisateur : " . $conn->error;
    }
} catch (Exception $e) {
    // Log the error and set a generic error message
    error_log("Erreur de suppression d'utilisateur : " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur s'est produite lors de la suppression de l'utilisateur.";
}

// Redirect back to the user management page
header('Location: users.php');
exit();