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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }

        .form-control:focus {
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
    </style>
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
            <div class="col-lg-8">
                <div class="card mb-5 fade-in">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-plus-circle me-2"></i>Ajouter un type de vaccin
                        </h3>
                        <form action="inserer_type_vaccin.php" method="POST" id="vaccineTypeForm">
                            <div class="mb-4">
                                <label for="name" class="form-label">Nom du vaccin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-prescription-bottle-medical"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="recommended_doses" class="form-label">Doses recommandées</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                                    <input type="number" class="form-control" id="recommended_doses" name="recommended_doses" min="1" required>
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
								<i class="fas fa-list me-2"></i>Liste des vaccins
							</h3>
							<div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchInput" placeholder="Rechercher un vaccin...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
							</div>
						</div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
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
                                            <a href="?delete_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
		// Fonction de recherche dans le tableau
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
		
        document.getElementById('vaccineTypeForm').addEventListener('submit', function(event) {
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