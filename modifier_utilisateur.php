<?php
require_once 'db.php';
session_start();

// Implement basic authentication check
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Aucun utilisateur spécifié.";
    header('Location: users.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Fetch user details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur non trouvé.";
        header('Location: users.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Erreur de récupération de l'utilisateur : " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur de récupération des informations utilisateur.";
    header('Location: users.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validation
    $errors = [];
    if (empty($username)) $errors[] = "Le nom d'utilisateur est requis.";
    if (empty($nom)) $errors[] = "Le nom est requis.";
    if (empty($prenom)) $errors[] = "Le prénom est requis.";

    // Check if username already exists (excluding current user)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    if ($count > 0) {
        $errors[] = "Ce nom d'utilisateur existe déjà.";
    }

    if (empty($errors)) {
        try {
            // Prepare update query
            if (!empty($password)) {
                // Hash password if provided
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, nom = ?, prenom = ?, role = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $nom, $prenom, $role, $hashed_password, $user_id);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET username = ?, nom = ?, prenom = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $nom, $prenom, $role, $user_id);
            }

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Utilisateur modifié avec succès.";
                header('Location: users.php');
                exit();
            } else {
                $errors[] = "Erreur lors de la mise à jour de l'utilisateur : " . $conn->error;
            }
        } catch (Exception $e) {
            error_log("Erreur de mise à jour de l'utilisateur : " . $e->getMessage());
            $errors[] = "Erreur lors de la mise à jour de l'utilisateur.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3>Modifier un Utilisateur</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" 
                               value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" 
                               value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                            <option value="editeur" <?php echo $user['role'] === 'editeur' ? 'selected' : ''; ?>>Éditeur</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe (laisser vide si inchangé)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="mb-3">
                        <a href="users.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>