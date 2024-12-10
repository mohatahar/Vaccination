<?php
include 'db.php';
include 'header.php';

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM vaccine_types WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
            Vaccin supprimé avec succès.
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
}

$query = "SELECT * FROM vaccine_types";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des types de vaccins</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style2.css">
</head>

<body>
    <div class="page-header text-center">
        <div class="container">
            <h1><i class="fas fa-vial"></i>Gestion des types de vaccins</h1>
            <p class="lead">Ajoutez et gérez les différents types de vaccins</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card mb-5 fade-in">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-plus-circle me-2"></i>Ajouter un type de vaccin
                        </h3>
                        <form action="inserer_type_vaccin.php" method="POST" id="vaccineTypeForm">
                            <div class="mb-4">
                                <label for="name" class="form-label">Type du vaccin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i
                                            class="fas fa-prescription-bottle-medical"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="recommended_doses" class="form-label">Doses recommandées</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                                    <input type="number" class="form-control" id="recommended_doses"
                                        name="recommended_doses" min="1" required>
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

                <div class="card fade-in">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title mb-4">
                                <i class="fas fa-list me-2"></i>Liste de types vaccins
                            </h3>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Rechercher un vaccin...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Doses Recommandées</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $row['recommended_doses']; ?> dose(s)
                                                </span>
                                            </td>
                                            <td class="text-center action-buttons">
                                                <a href="modifier_type_vaccin.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce vaccin ?');">
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
    <script>
        // Fonction de recherche dans le tableau
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        document.getElementById('vaccineTypeForm').addEventListener('submit', function (event) {
            event.preventDefault();
            var form = this;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        var response = xhr.responseText.trim();

                        if (response.includes("succès")) {
                            if (confirm("Ajout avec succès. Voulez-vous ajouter un autre type ?")) {
                                form.reset();
                                window.location.href = "ajouter_vaccin.php";
                            }
                        } else {
                            alert(response);
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