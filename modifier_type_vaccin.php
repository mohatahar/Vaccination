<?php
// On démarre la session avant tout output
session_start();

include 'db.php'; // Connexion à la base de données

$message = ''; // Variable pour stocker les messages

// Vérifie si l'ID du vaccin est passé via GET
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID du vaccin non spécifié.";
    header("Location: ajouter_vaccin.php");
    exit;
}

$id = $_GET['id'];

// Récupère les détails du vaccin sélectionné
$query = "SELECT * FROM vaccine_types WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$vaccine = $result->fetch_assoc();

if (!$vaccine) {
    $_SESSION['error'] = "Vaccin non trouvé.";
    header("Location: ajouter_vaccin.php");
    exit;
}

// Mise à jour des données
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $recommended_doses = $_POST['recommended_doses'];

    $update_query = "UPDATE vaccine_types SET name = ?, recommended_doses = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('sii', $name, $recommended_doses, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Type de vaccin mis à jour avec succès.";
        header("Location: ajouter_vaccin.php");
        exit;
    } else {
        $message = "Erreur lors de la mise à jour.";
    }
}

// Maintenant on peut inclure le header et commencer l'affichage HTML
include 'header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un type de vaccin</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-primary {
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }
        .btn-secondary {
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 500;
        }
        .page-title {
            color: #2c3e50;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="page-title mb-0">
                            <i class="fas fa-syringe me-2"></i>
                            Modifier le type de vaccin
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="mb-4">
                                <label for="name" class="form-label">
                                    <i class="fas fa-prescription-bottle-medical me-2"></i>
                                    Nom du vaccin
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($vaccine['name']); ?>" 
                                       required>
                            </div>
                            <div class="mb-4">
                                <label for="recommended_doses" class="form-label">
                                    <i class="fas fa-list-ol me-2"></i>
                                    Doses recommandées
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="recommended_doses" 
                                       name="recommended_doses" 
                                       value="<?php echo htmlspecialchars($vaccine['recommended_doses']); ?>" 
                                       min="1" 
                                       required>
                            </div>
							<div class="text-end">
								<a href="ajouter_vaccin.php" class="btn btn-secondary">
									<i class="fas fa-times me-2"></i> Annuler
								</a>
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-save me-2"></i> Enregistrer les modifications
								</button>
							</div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>