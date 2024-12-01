<?php
include 'db.php';
include 'header.php';

// Vérification si l'ID de la vaccination est passé en paramètre
if (isset($_GET['id'])) {
    $vaccination_id = $_GET['id'];

    // Récupération des informations de la vaccination actuelle
    $query = "
        SELECT 
            employee_id, 
            type_vaccin, 
            date_vaccination, 
            dose, 
            prochain_rappel 
        FROM vaccinations 
        WHERE id = ?";

    // Préparer la requête
    if ($stmt = $conn->prepare($query)) {
        // Liaison des paramètres
        $stmt->bind_param('i', $vaccination_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Vérifier si un enregistrement a été trouvé
        if ($result->num_rows > 0) {
            $vaccination = $result->fetch_assoc();
        } else {
            echo "<p class='alert alert-danger'>Aucune vaccination trouvée avec cet ID.</p>";
            exit;
        }
    } else {
        // Afficher l'erreur SQL
        echo "<p class='alert alert-danger'>Erreur dans la requête SQL : " . $conn->error . "</p>";
        exit;
    }
}

// Gestion de la modification de la vaccination
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $type_vaccin = $_POST['type_vaccin'];
    $date_vaccination = $_POST['date_vaccination'];
    $dose = $_POST['dose'];
    $prochain_rappel = $value_prochain_rappel = isset($vaccination['prochain_rappel']) ? htmlspecialchars($vaccination['prochain_rappel']) : '';
    $rappel_effectue = 'oui';

    // Mise à jour des informations dans la base de données
    $update_query = "
    UPDATE vaccinations 
    SET  rappel_effectue = ? 
    WHERE id = ?";

    if ($stmt = $conn->prepare($update_query)) {
        // Remplacement du 'i' par un 's' pour type_vaccin (car c'est une chaîne de caractères)
        $stmt->bind_param('si', $rappel_effectue, $vaccination_id);
        $stmt->execute();

        // Récupération des valeurs du formulaire
        $employee_id = $_POST['employee_id'];
        $type_vaccin_id = $_POST['type_vaccin']; // Ce sera l'ID du vaccin sélectionné dans le menu déroulant
        $date_vaccination = $_POST['date_vaccination'];
        $dose = $_POST['dose'];
        $prochain_rappel = $_POST['prochain_rappel'];

        // Vérifier si le prochain rappel est calculé automatiquement

        $rappel_effectue = "oui";

        // Récupérer les informations du type de vaccin (recommandations sur les doses et intervalle de rappel)
        $sql = "SELECT name, recommended_doses FROM vaccine_types WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $type_vaccin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $type_vaccin_name = $row['name'];
            $recommended_doses = $row['recommended_doses'];

            // Déterminer si c'est la dernière dose
            if ($dose >= $recommended_doses) {
                $prochain_rappel = null;
                $rappel_effectue = "oui";
            } else {
                $prochain_rappel = $_POST['prochain_rappel'];
                $rappel_effectue = "non";
            }

            // Insertion dans la table vaccinations
            $sql_insert = "INSERT INTO vaccinations (employee_id, type_vaccin, date_vaccination, dose, prochain_rappel, rappel_effectue)
        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("isssss", $employee_id, $type_vaccin_name, $date_vaccination, $dose, $prochain_rappel, $rappel_effectue);

            // Exécution de la requête et gestion des erreurs
            if ($stmt_insert->execute()) {
                echo "<script>
            alert('Rappel fait avec succès');
            window.location.href = 'ajouter_vaccination.php';
          </script>";
            } else {
                echo "Erreur: " . $stmt_insert->error;
            }

            $stmt_insert->close();
        } else {
            echo "Erreur : Type de vaccin introuvable";
        }


    } else {
        // Afficher l'erreur SQL
        echo "<p class='alert alert-danger'>Erreur dans la requête SQL : " . $conn->error . "</p>";
    }


}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Vaccinations</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/flatpickr/flatpickr.min.css">
    <link href="assets/select2/select2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
            color: #2d3748;
        }

        .page-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #0062cc 0%, #0096ff 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .page-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.75rem;
        }

        .section-title {
            color: #4a5568;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.625rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .required-field::after {
            content: " *";
            color: #e53e3e;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0062cc 0%, #0096ff 100%);
            border: none;
            box-shadow: 0 2px 4px rgba(0, 98, 204, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 98, 204, 0.3);
        }

        .btn-secondary {
            background: #718096;
            border: none;
        }

        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-icon {
            color: #718096;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }

        .field-info {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .page-container {
                margin: 1rem auto;
            }

            .form-card {
                padding: 1.5rem;
            }

            .page-header {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="page-container">
        <!-- En-tête de la page -->
        <header class="page-header">
            <h2>Vaccin Rappel</h2>
            <p class="mb-0 mt-2">Enregistrement et suivi de vaccin rappel</p>
        </header>

        <!-- Formulaire principal -->
        <div class="form-card">
            <?php if (isset($message))
                echo $message; ?>

            <form method="POST" id="vaccinationForm">
                <!-- Section Informations de base -->
                <div class="section-title">Informations principales</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="employee_id" class="form-label required-field">Employé</label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Sélectionner un employé...</option>
                                <?php
                                $result = $conn->query("SELECT id, nom, prenom FROM employees ORDER BY nom, prenom");
                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($vaccination && $row['id'] == $vaccination['employee_id']) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>"
                                        . htmlspecialchars($row['nom']) . " " . htmlspecialchars($row['prenom']) . "</option>";
                                }
                                ?>
                            </select>
                            <div class="field-info">Sélectionnez l'employé concerné par la vaccination</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type_vaccin" class="form-label required-field">Type de vaccin</label>
                            <select class="form-select" id="type_vaccin" name="type_vaccin" required>
                                <option value="">Sélectionner un type de vaccin...</option>
                                <?php
                                $result = $conn->query("SELECT id, name FROM vaccine_types ORDER BY name");
                                while ($row = $result->fetch_assoc()) {
                                    // Modifie cette ligne pour comparer avec type_vaccin de la table vaccinations
                                    $selected = ($vaccination && $row['name'] == $vaccination['type_vaccin']) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>"
                                        . htmlspecialchars($row['name']) . "</option>";
                                }
                                ?>
                            </select>
                            <div class="field-info">Choisissez le type de vaccin administré</div>
                        </div>
                    </div>
                </div>

                <!-- Section Détails de la vaccination -->
                <div class="section-title mt-4">Détails de la vaccination</div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_vaccination" class="form-label required-field">Date
                                d'administration</label>
                            <input type="text" class="form-control" id="date_vaccination" name="date_vaccination"
                                value="<?php echo $vaccination ? htmlspecialchars($vaccination['date_vaccination']) : ''; ?>"
                                required>
                            <div class="field-info">Date à laquelle le vaccin a été administré</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="dose" class="form-label required-field">Numéro de dose</label>
                            <input type="number" class="form-control" id="dose" name="dose"
                                value="<?php echo $vaccination ? htmlspecialchars($vaccination['dose'] + 1) : '1'; ?>"
                                min="1" required>
                            <div class="field-info">Numéro de la dose administrée</div>
                        </div>
                    </div>
                    <div class="col-md-6" id="prochain_rappel_container" style="display: none;">
                        <div class="form-group">
                            <label for="prochain_rappel" class="form-label">Date du prochain rappel</label>
                            <input type="text" class="form-control" id="prochain_rappel" name="prochain_rappel">
                            <div class="field-info">Date du prochain rappel s'il existe</div>
                        </div>
                    </div>
                </div>

                <!-- Pied de formulaire -->
                <div class="form-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="ajouter_vaccination.php" class="btn btn-secondary">
                            Retour à la liste
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Enregistrer la vaccination
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/flatpickr/flatpickr.js"></script>
    <script src="assets/flatpickr/fr.js"></script>
    <script src="assets/select2/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configuration de Flatpickr
            flatpickr("#date_vaccination", {
                locale: "fr",
                dateFormat: "Y-m-d",
                maxDate: "today",
                allowInput: true,
                theme: "light",
                defaultDate: "today"
            });

            flatpickr("#prochain_rappel", {
                locale: "fr",
                dateFormat: "Y-m-d",
                allowInput: true,
                theme: "light"
            });

            // Configuration de Select2
            $('#employee_id, #type_vaccin').select2({
                placeholder: "Rechercher...",
                allowClear: true,
                width: '100%',
                theme: "classic"
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            // Configuration existante de Flatpickr et Select2...

            // Fonction pour vérifier si c'est la dernière dose
            function checkDoseNumber() {
                const typeVaccinSelect = document.getElementById('type_vaccin');
                const doseInput = document.getElementById('dose');
                const prochainRappelContainer = document.getElementById('prochain_rappel_container');
                const prochainRappelInput = document.getElementById('prochain_rappel');

                if (typeVaccinSelect.value) {
                    // Faire une requête AJAX pour obtenir le nombre de doses recommandées
                    fetch('get_vaccine_info.php?id=' + typeVaccinSelect.value)
                        .then(response => response.json())
                        .then(data => {
                            if (parseInt(doseInput.value) >= parseInt(data.recommended_doses)) {
                                // C'est la dernière dose ou plus
                                prochainRappelContainer.style.display = 'none';
                                prochainRappelInput.value = ''; // Vider la valeur
                                prochainRappelInput.required = false;
                            } else {
                                // Ce n'est pas la dernière dose
                                prochainRappelContainer.style.display = 'block';
                                prochainRappelInput.required = true;
                            }
                        });
                }
            }

            // Ajouter les écouteurs d'événements
            document.getElementById('type_vaccin').addEventListener('change', checkDoseNumber);
            document.getElementById('dose').addEventListener('change', checkDoseNumber);

            // Vérifier au chargement de la page
            checkDoseNumber();
        });
    </script>
</body>

</html>