<?php
include 'db.php';
include 'header.php';

// Suppression d'un employé
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM employees WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    echo "<p class='alert alert-success'>Employé supprimé avec succès.</p>";
}

// Récupération de tous les employés
$query = "SELECT * FROM employees";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Employés</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="css/style2.css">
    <style>
        /* Conteneur pour les boutons */
        .buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-end;
            /* Aligner les boutons à droite */
        }

        /* Boutons stylisés */
        .button {
            background-color: #333;
            /* Fond noir */
            color: #fff;
            /* Texte blanc */
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .button:hover {
            background-color: #555;
            /* Survol */
        }
    </style>
</head>

<body>
    <div class="page-header text-center">
        <div class="container">
            <h1><i class="fas fa-users me-2"></i>Gestion des Employés</h1>
            <p class="lead">Ajoutez et gérez les informations du personnel</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Formulaire d'ajout -->
                <div class="card mb-5 fade-in">
                    <div class="card-body">
                        <div class="buttons">
                            <a href="importer_employe.php" class="button">
                                <i class="fas fa-file-import"></i> Importer une liste d'employés
                            </a>
                        </div>
                        <h3 class="card-title mb-4">
                            <i class="fas fa-user-plus me-2"></i>Ajouter un nouvel employé
                        </h3>
                        <form action="inserer_employe.php" method="POST" id="employeForm">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="nom" class="form-label required-field">Nom</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="nom" name="nom" required
                                            placeholder="Entrez le nom">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="prenom" class="form-label required-field">Prénom</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="prenom" name="prenom" required
                                            placeholder="Entrez le prénom">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="sexe" class="form-label required-field">Sexe</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                        <select class="form-select" id="sexe" name="sexe" required>
                                            <option value="">Sélectionnez...</option>
                                            <option value="Homme">Homme</option>
                                            <option value="Femme">Femme</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="date_naissance" class="form-label required-field">Date de
                                        naissance</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" class="form-control" id="date_naissance"
                                            name="date_naissance" required placeholder="Sélectionnez la date">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="telephone" class="form-label required-field">N° Téléphone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" id="telephone" name="telephone" required
                                            placeholder="Ex: 0600000000">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="grade" class="form-label required-field">Grade</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-star"></i></span>
                                        <input type="text" class="form-control" id="grade" name="grade" required
                                            placeholder="Entrez le grade">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="service" class="form-label required-field">Service</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" class="form-control" id="service" name="service" required
                                            placeholder="Entrez le service">
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="reset" class="btn btn-secondary me-2">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des employés -->
                <div class="card fade-in">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>Liste des employés
                            </h3>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Rechercher un employé...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                        <th><i class="fas fa-user me-2"></i>Nom</th>
                                        <th><i class="fas fa-user me-2"></i>Prénom</th>
                                        <th><i class="fas fa-venus-mars me-2"></i>Sexe</th>
                                        <th><i class="fas fa-calendar me-2"></i>Date de naissance</th>
                                        <th><i class="fas fa-star me-2"></i>Grade</th>
                                        <th><i class="fas fa-building me-2"></i>Service</th>
                                        <th><i class="fas fa-phone me-2"></i>N° Telephone</th>
                                        <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1;
                                    while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nom']); ?></td>
                                            <td><?php echo htmlspecialchars($row['prenom']); ?></td>
                                            <td><?php echo $row['sexe']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['date_naissance'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['grade']); ?></td>
                                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                                            <td><?php echo htmlspecialchars($row['telephone']); ?></td>
                                            <td class="btn-group-action">
                                                <a href="modifier_employe.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/flatpickr/flatpickr.js"></script>
    <script>
        // Configuration de Flatpickr pour la date
        flatpickr("#date_naissance", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            locale: "fr"
        });

        // Fonction de recherche dans le tableau
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Gestion du formulaire
        document.getElementById('employeForm').addEventListener('submit', function (event) {
            event.preventDefault();

            var form = this;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        if (confirm("L'employé a été ajouté avec succès. Voulez-vous ajouter un autre employé ?")) {
                            form.reset();
                            window.location.href = "ajouter_employe.php";
                        }
                    } else {
                        alert("Erreur lors de l'ajout : " + xhr.responseText);
                    }
                }
            };

            var formData = new FormData(form);
            xhr.send(new URLSearchParams(formData).toString());
        });
    </script>
</body>

</html>

<?php include 'footer.php'; ?>