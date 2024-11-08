<?php
// Approche 1: Activer la mise en mémoire tampon au tout début du script
ob_start();

include 'db.php';
include 'header.php';

// Variables pour stocker les messages et l'état de redirection
$success_message = '';
$error_message = '';
$should_redirect = false;

// Récupérer l'ID de l'employé à partir de l'URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employe = $result->fetch_assoc();

    if (!$employe) {
        $error_message = 'Aucun employé trouvé avec cet ID.';
    }
} else {
    $error_message = 'ID non fourni.';
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];
    $grade = $_POST['grade'];
    $service = $_POST['service'];
    $telephone = $_POST['telephone'];

    $update_query = "UPDATE employees SET nom = ?, prenom = ?, sexe = ?, date_naissance = ?, grade = ?, service = ?, telephone = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssssi", $nom, $prenom, $sexe, $date_naissance, $grade, $service, $telephone, $id);

    if ($stmt->execute()) {
        $should_redirect = true;
    } else {
        $error_message = 'Erreur lors de la mise à jour.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Employé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fb;
            font-family: 'Inter', sans-serif;
            color: #2d3748;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
            padding: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }
        .btn-primary {
            background-color: #4299e1;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #3182ce;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: #718096;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #4a5568;
            transform: translateY(-1px);
        }
        .page-header {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .form-section {
            margin-bottom: 2rem;
        }
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 1rem;
        }
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php if ($should_redirect): ?>
        <!-- Approche 2: Redirection JavaScript -->
        <script>
            window.location.href = 'ajouter_employe.php';
        </script>
    <?php endif; ?>

    <div class="container mt-5 mb-5">
        <div class="card">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!$error_message || $error_message === 'Erreur lors de la mise à jour.'): ?>
                <h2 class="page-header">Modifier les informations de l'employé</h2>
                
                <form action="" method="POST">
                    <div class="form-section">
                        <h3 class="form-section-title">Informations personnelles</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($employe['nom']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($employe['prenom']); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="sexe" class="form-label">Sexe</label>
                                <select class="form-select" id="sexe" name="sexe" required>
                                    <option value="M" <?php echo $employe['sexe'] == 'M' ? 'selected' : ''; ?>>Masculin</option>
                                    <option value="F" <?php echo $employe['sexe'] == 'F' ? 'selected' : ''; ?>>Féminin</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="text" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($employe['date_naissance']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="telephone" class="form-label">N° Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($employe['telephone']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Informations professionnelles</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="grade" class="form-label">Grade</label>
                                <input type="text" class="form-control" id="grade" name="grade" value="<?php echo htmlspecialchars($employe['grade']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="service" class="form-label">Service</label>
                                <input type="text" class="form-control" id="service" name="service" value="<?php echo htmlspecialchars($employe['service']); ?>" required>
                            </div>
                        </div>
                    </div>
					
					<div class="text-end">
						<a href="ajouter_employe.php" class="btn btn-secondary">
							<i class="fas fa-times me-2"></i> Annuler
						</a>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-2"></i> Enregistrer les modifications
						</button>
					</div>
                 </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#date_naissance", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            locale: "fr"
        });
    </script>
</body>
</html>
<?php
// Approche 1: Vider et fermer la mise en mémoire tampon à la fin du script
ob_end_flush();
?>