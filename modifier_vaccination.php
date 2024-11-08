<?php
// Démarrer la session et le buffer de sortie au tout début du fichier
ob_start();
session_start();

include 'db.php';
include 'header.php';

// Initialisation des variables
$messages = [];
$vaccination = null;
$error = false;

// Vérification si l'ID de la vaccination est passé en paramètre
if (!isset($_GET['id'])) {
    $messages[] = ["type" => "danger", "text" => "Aucun ID de vaccination spécifié."];
    $error = true;
} else {
    $vaccination_id = $_GET['id'];
    
    $query = "SELECT v.*, e.nom, e.prenom 
              FROM vaccinations v
              JOIN employees e ON v.employee_id = e.id 
              WHERE v.id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('i', $vaccination_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $vaccination = $result->fetch_assoc();
        } else {
            $messages[] = ["type" => "danger", "text" => "Aucune vaccination trouvée avec cet ID."];
            $error = true;
        }
        $stmt->close();
    } else {
        $messages[] = ["type" => "danger", "text" => "Erreur dans la requête SQL : " . $conn->error];
        $error = true;
    }
}

// Gestion de la modification de la vaccination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $employee_id = $_POST['employee_id'];
    $type_vaccin = $_POST['type_vaccin'];
    $date_vaccination = $_POST['date_vaccination'];
    $dose = $_POST['dose'];
    $prochain_rappel = $_POST['prochain_rappel'];

    // Validation des données
    $validation_errors = [];
    
    if (empty($employee_id)) {
        $validation_errors[] = "L'employé est requis.";
    }
    if (empty($type_vaccin)) {
        $validation_errors[] = "Le type de vaccin est requis.";
    }
    if (empty($date_vaccination)) {
        $validation_errors[] = "La date de vaccination est requise.";
    }
    if (empty($dose) || !is_numeric($dose) || $dose < 1) {
        $validation_errors[] = "La dose doit être un nombre positif.";
    }
    if (empty($prochain_rappel)) {
        $validation_errors[] = "La date du prochain rappel est requise.";
    }

    if (empty($validation_errors)) {
        $update_query = "UPDATE vaccinations 
                        SET employee_id = ?, type_vaccin = ?, date_vaccination = ?, 
                            dose = ?, prochain_rappel = ? 
                        WHERE id = ?";

        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param('issssi', $employee_id, $type_vaccin, $date_vaccination, 
                             $dose, $prochain_rappel, $vaccination_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Vaccination modifiée avec succès.";
                header("Location: ajouter_vaccination.php");
                exit();
            } else {
                $messages[] = ["type" => "danger", "text" => "Erreur lors de la mise à jour : " . $stmt->error];
            }
            $stmt->close();
        } else {
            $messages[] = ["type" => "danger", "text" => "Erreur dans la requête SQL : " . $conn->error];
        }
    } else {
        foreach ($validation_errors as $error) {
            $messages[] = ["type" => "danger", "text" => $error];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Vaccination</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            background-color: var(--background-color);
            color: var(--primary-color);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .page-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: none;
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--secondary-color);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }

        .input-group-text {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }

        .select2-container .select2 {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 48px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="card-form">
            <h2 class="page-title">
                <i class="fas fa-syringe me-2"></i>
                Modifier la vaccination
            </h2>
            
            <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>" role="alert">
                    <i class="fas fa-<?php echo $message['type'] === 'danger' ? 'exclamation-triangle' : 'check-circle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endforeach; ?>

            <?php if ($vaccination): ?>
            <form action="" method="POST">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-md me-2"></i>
                        Informations principales
                    </h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">Employé</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <select class="form-select" id="employee_id" name="employee_id" required>
                                    <option value="">Sélectionner un employé...</option>
                                    <?php
                                    $result = $conn->query("SELECT id, nom, prenom FROM employees ORDER BY nom, prenom");
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = ($row['id'] == $vaccination['employee_id']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . 
                                             htmlspecialchars($row['nom']) . " " . htmlspecialchars($row['prenom']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type_vaccin" class="form-label">Type de vaccin</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-prescription-bottle-medical"></i>
                                </span>
                                <select class="form-select" id="type_vaccin" name="type_vaccin" required>
                                    <option value="">Sélectionner un type de vaccin...</option>
                                    <?php
                                    $result = $conn->query("SELECT name FROM vaccine_types ORDER BY name");
                                    while ($row = $result->fetch_assoc()) {
                                        $selected = ($row['name'] == $vaccination['type_vaccin']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($row['name']) . "' $selected>" . 
                                             htmlspecialchars($row['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Dates et dosage
                    </h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date_vaccination" class="form-label">Date de vaccination</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="text" class="form-control" id="date_vaccination" name="date_vaccination" 
                                       value="<?php echo htmlspecialchars($vaccination['date_vaccination']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dose" class="form-label">Dose</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input type="number" class="form-control" id="dose" name="dose" 
                                       value="<?php echo htmlspecialchars($vaccination['dose']); ?>" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prochain_rappel" class="form-label">Prochain Rappel</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <input type="text" class="form-control" id="prochain_rappel" name="prochain_rappel" 
                                       value="<?php echo htmlspecialchars($vaccination['prochain_rappel']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

               <div class="text-end">
					<a href="ajouter_vaccination.php" class="btn btn-secondary">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        flatpickr("#date_vaccination", {
            locale: "fr",
            dateFormat: "Y-m-d",
            maxDate: "today",
            theme: "airbnb"
        });
        
        flatpickr("#prochain_rappel", {
            locale: "fr",
            dateFormat: "Y-m-d",
            minDate: "today",
            theme: "airbnb"
        });

        $(document).ready(function() {
            $('#employee_id').select2({
                theme: "classic",
                placeholder: "Rechercher un employé",
                allowClear: true
            });

            $('#type_vaccin').select2({
                theme: "classic",
                placeholder: "Sélectionner un type de vaccin",
                allowClear: true
            });
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>