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
     <link rel="stylesheet" href="css/style2.css">
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
            <div class="col-lg-11">
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