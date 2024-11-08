<?php 
include 'db.php';
include 'header.php';

// Gestion de la suppression
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM vaccinations WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    echo "<p class='alert alert-success'>Vaccination supprimé avec succès.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Vaccinations</title>
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
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .form-control, .form-select, .select2-container .select2-selection--single {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .table {
            background-color: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            border: none;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .action-buttons .btn {
            margin: 0 0.25rem;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Select2 customization */
        .select2-container .select2-selection--single {
            height: 45px;
            border-radius: 0.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 45px;
            padding-left: 1rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
            right: 1rem;
        }

        .input-group-text {
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .btn-group-action {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
	<div class="page-header text-center">
        <div class="container">
            <h1><i class="fas fa-syringe me-2"></i>Gestion des vaccinations</h1>
            <p class="lead">Ajoutez et gérez les vaccinations des employés</p>
        </div>
    </div>
	
     <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Formulaire d'ajout -->
                <div class="card mb-5 fade-in">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-plus-circle me-2"></i>Ajouter une vaccination
                        </h3>
                        <form action="inserer_vaccination.php" method="POST" id="vaccinationForm">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="employee_id" class="form-label">Employé</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <select class="form-select" id="employee_id" name="employee_id" required>
                                            <option value="">Sélectionner un employé...</option>
                                            <?php
                                            $result = $conn->query("SELECT id, nom, prenom FROM employees ORDER BY nom, prenom");
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value='" . htmlspecialchars($row['id']) . "'>" . 
                                                     htmlspecialchars($row['nom']) . " " . htmlspecialchars($row['prenom']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="type_vaccin" class="form-label">Type de vaccin</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-prescription-bottle-medical"></i></span>
                                        <select class="form-select" id="type_vaccin" name="type_vaccin" required>
                                            <option value="">Sélectionner un type de vaccin...</option>
                                            <?php
                                            $result = $conn->query("SELECT id, name FROM vaccine_types ORDER BY name");
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="date_vaccination" class="form-label">Date de vaccination</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" class="form-control" id="date_vaccination" name="date_vaccination" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="dose" class="form-label">Dose</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-prescription"></i></span>
                                        <input type="number" class="form-control" id="dose" name="dose" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="prochain_rappel" class="form-label">Prochain Rappel</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                        <input type="text" class="form-control" id="prochain_rappel" name="prochain_rappel" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
								<button type="reset" class="btn btn-secondary me-2">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Ajouter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

         <!-- Liste des vaccinations -->
                <div class="card fade-in">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
							<h3 class="card-title mb-4">
								<i class="fas fa-list me-2"></i>Liste des vaccinations
							</h3>
							<div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchInput" placeholder="Rechercher une vaccination...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
							</div>
						</div>
                        <?php
						$query = "SELECT 
									employees.nom, 
									employees.prenom, 
									vaccinations.type_vaccin, 
									vaccinations.date_vaccination, 
									vaccinations.dose, 
									vaccinations.prochain_rappel,
									vaccinations.rappel_effectue,
									vaccinations.id as vaccination_id
									FROM employees
									INNER JOIN vaccinations ON employees.id = vaccinations.employee_id
									ORDER BY vaccinations.date_vaccination DESC";

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-hover'>";
                    echo "<thead>
                            <tr>
                                <th><i class='fas fa-hashtag me-2'></i>ID</th>
                                <th><i class='fas fa-user me-2'></i>Nom</th>
                                <th><i class='fas fa-user me-2'></i>Prénom</th>
                                <th><i class='fas fa-syringe me-2'></i>Type de vaccin</th>
                                <th><i class='fas fa-calendar me-2'></i>Date de vaccination</th>
                                <th><i class='fas fa-prescription me-2'></i>Dose</th>
                                <th><i class='fas fa-clock me-2'></i>Prochain rappel</th>
                                <th><i class='fas fa-cogs me-2'></i>Actions</th>
                            </tr>
                          </thead>";
                    echo "<tbody>";
                    $i = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $i++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['prenom']) . "</td>";
                        echo "<td>" . (!empty($row['type_vaccin']) ? htmlspecialchars($row['type_vaccin']) : "N/A") . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['date_vaccination'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['dose']) . "</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['prochain_rappel'])) . "</td>";
                        echo "<td class='btn-group-action'>";
                        echo "<a href='modifier_vaccination.php?id=" . $row['vaccination_id'] . "' 
                                class='btn btn-warning btn-sm'>
                                <i class='fas fa-edit'></i>
                              </a>";
                        echo "<a href='?delete_id=" . $row['vaccination_id'] . "' 
                                class='btn btn-danger btn-sm' 
                                onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce vaccin ?\");'>
                                <i class='fas fa-trash'></i>
                              </a>";
                        
                        if($row['rappel_effectue'] == 'oui') {
                            echo "<button class='btn btn-success btn-sm' disabled>
                                    <i class='fas fa-check'></i>
                                  </button>";
                        } else {
                            echo "<a href='modifier_rappel.php?id=" . $row['vaccination_id'] . "' 
                                    class='btn btn-success btn-sm'>
                                    <i class='fas fa-bell'></i>
                                  </a>";
                        }
                        echo "</td></tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<div class='alert alert-info'>
                            <i class='fas fa-info-circle me-2'></i>
                            Aucune donnée de vaccination trouvée.
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Initialisation de Flatpickr pour les champs de date
        flatpickr("#date_vaccination", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            locale: "fr"
        });

        flatpickr("#prochain_rappel", {
            dateFormat: "Y-m-d",
            minDate: "today",
            locale: "fr"
        });
		
		// Fonction de recherche dans le tableau
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Initialisation de Select2 pour les sélecteurs
        $(document).ready(function() {
            $('#employee_id, #type_vaccin').select2({
                theme: "classic",
                placeholder: "Rechercher...",
                allowClear: true,
                width: '100%'
            });
        });

        // Validation du formulaire
        document.getElementById('vaccinationForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            var form = this;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        if (confirm("Vaccination ajoutée avec succès. Voulez-vous ajouter une autre vaccination ?")) {
                            form.reset();
                            $('#employee_id, #type_vaccin').val(null).trigger('change');
                            window.location.href = "ajouter_vaccination.php";
                        }
                    } else {
                        alert("Erreur lors de l'ajout : " + xhr.responseText);
                    }
                }
            };

            var formData = new FormData(form);
            xhr.send(new URLSearchParams(formData).toString());
        });
		
		// Ajoutez ceci dans la section $(document).ready()
		$(document).ready(function() {
			$('#employee_id, #type_vaccin').select2({
			theme: "classic",
			placeholder: "Rechercher...",
			allowClear: true,
			width: '100%'
		});

		// Gestionnaire d'événement pour le bouton de réinitialisation
		document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
        // Empêcher le comportement par défaut
        e.preventDefault();
        
        // Réinitialiser le formulaire standard
        document.getElementById('vaccinationForm').reset();
        
        // Réinitialiser les select2
        $('#employee_id, #type_vaccin').val(null).trigger('change');
        
        // Réinitialiser les dates
        document.getElementById('date_vaccination').value = '';
        document.getElementById('prochain_rappel').value = '';
		});
		});
    </script>
</body>
</html>