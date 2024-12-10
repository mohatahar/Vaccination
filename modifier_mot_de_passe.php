<?php
require_once 'auth_check.php';
require_once 'db.php';
include 'header.php';

$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Vérification que tous les champs sont remplis
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Tous les champs sont obligatoires.";
        $messageType = "danger";
    }
    // Vérification que le nouveau mot de passe et la confirmation correspondent
    elseif ($new_password !== $confirm_password) {
        $message = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
        $messageType = "danger";
    }
    else {
        try {
            // Récupérer le mot de passe actuel de l'utilisateur
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $_SESSION['user_id']);  // "i" pour integer (id)
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Vérifier si l'utilisateur existe
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Vérifier si le mot de passe actuel est correct
                if (password_verify($current_password, $user['password'])) {
                    // Hasher le nouveau mot de passe
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Mettre à jour le mot de passe
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']); // "s" pour string et "i" pour integer
                    if ($update_stmt->execute()) {
                        $message = "Votre mot de passe a été modifié avec succès.";
                        $messageType = "success";
                    } else {
                        $message = "Une erreur est survenue lors de la mise à jour du mot de passe.";
                        $messageType = "danger";
                    }
                } else {
                    $message = "Le mot de passe actuel est incorrect.";
                    $messageType = "danger";
                }
            } else {
                $message = "Utilisateur introuvable.";
                $messageType = "danger";
            }
        } catch (Exception $e) {
            $message = "Une erreur est survenue lors de la modification du mot de passe.";
            $messageType = "danger";
        }
    }
}

// Configuration de la page
$page_title = "Modifier le mot de passe";
$show_page_header = true;
$page_header_icon = "fas fa-key";
$page_header_title = "Modification du mot de passe";
$page_header_description = "Modifiez votre mot de passe en toute sécurité";

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification de mot de passe</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
</head>

<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            <div class="invalid-feedback">
                                Veuillez saisir votre mot de passe actuel
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="new_password" 
                                   name="new_password" 
                                   required>
                            <div class="invalid-feedback">
                                Veuillez saisir un nouveau mot de passe
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required>
                            <div class="invalid-feedback">
                                Veuillez confirmer votre nouveau mot de passe
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Modifier le mot de passe
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/bootstrap/bootstrap.min.js"></script>
<!-- Script pour la validation côté client -->
<script>
(function () {
    'use strict'
    
    // Récupérer tous les formulaires auxquels nous voulons appliquer des styles de validation Bootstrap personnalisés
    var forms = document.querySelectorAll('.needs-validation')
    
    // Empêcher la soumission et appliquer la validation
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                // Vérifier si les mots de passe correspondent
                var newPassword = document.getElementById('new_password')
                var confirmPassword = document.getElementById('confirm_password')
                
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas')
                    event.preventDefault()
                    event.stopPropagation()
                } else {
                    confirmPassword.setCustomValidity('')
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

</body>

</html>

<?php include 'footer.php'; ?>
