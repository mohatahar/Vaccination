<?php
ob_start();

require_once 'auth_check.php';
require_once 'db.php';
include 'header.php';

// Vérifier si l'utilisateur est connecté (ajoutez votre logique de connexion)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer le numéro de lot à modifier
$lot_number = isset($_GET['lot_number']) ? $_GET['lot_number'] : null;

if (!$lot_number) {
    $_SESSION['error_message'] = "Numéro de lot non spécifié.";
    header('Location: vaccin.php');
    exit();
}

// Récupérer les informations actuelles du stock
$stmt = $conn->prepare("SELECT * FROM entrees_stock WHERE lot_number = ?");
$stmt->bind_param("s", $lot_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Stock non trouvé.";
    header('Location: vaccin.php');
    exit();
}

$stock = $result->fetch_assoc();

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $type_vaccin = $_POST['type_vaccin'];
    $date_reception = $_POST['date_reception'];
    $date_expiration = $_POST['date_expiration'];
    $quantite_recu = $_POST['quantite_recu'];

    // Préparer la requête de mise à jour
    $update_stmt = $conn->prepare("UPDATE entrees_stock SET 
        nom = ?, 
        type_vaccin = ?, 
        date_reception = ?, 
        date_expiration = ?, 
        quantite_recu = ? 
        WHERE lot_number = ?");
    
    $update_stmt->bind_param("ssssss", 
        $nom, 
        $type_vaccin, 
        $date_reception, 
        $date_expiration, 
        $quantite_recu, 
        $lot_number
    );

    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Stock mis à jour avec succès.";
        header('Location: vaccin.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $update_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Stock de Vaccins</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Modifier Stock de Vaccin</h2>
    
    <?php
    // Afficher les messages de succès ou d'erreur
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nom du Vaccin</label>
            <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($stock['nom']); ?>" required>
        </div>

        <div class="form-group">
            <label>Type de Vaccin</label>
            <input type="text" name="type_vaccin" class="form-control" value="<?php echo htmlspecialchars($stock['type_vaccin']); ?>" required>
        </div>

        <div class="form-group">
            <label>Numéro de Lot</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($stock['lot_number']); ?>" readonly>
        </div>

        <div class="form-group">
            <label>Date de Réception</label>
            <input type="date" name="date_reception" class="form-control" value="<?php echo htmlspecialchars($stock['date_reception']); ?>" required>
        </div>

        <div class="form-group">
            <label>Date d'Expiration</label>
            <input type="date" name="date_expiration" class="form-control" value="<?php echo htmlspecialchars($stock['date_expiration']); ?>" required>
        </div>

        <div class="form-group">
            <label>Quantité Reçu</label>
            <input type="number" name="quantite_recu" class="form-control" value="<?php echo htmlspecialchars($stock['quantite_recu']); ?>" required min="0">
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="vaccin.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
<script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>