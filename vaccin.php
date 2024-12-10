<?php
include 'db.php';
include 'header.php';

// Traitement du formulaire pour ajouter un vaccin
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $type_vaccin = $_POST['type_vaccin'];
    $lot_number = $_POST['lot_number'];
    $date_reception = $_POST['date_reception'];
    $date_expiration = $_POST['date_expiration'];
    $quantite_recu = $_POST['quantite_recu'];
    $quantite_disponible = $_POST['quantite_recu'];

    // Ajouter le vaccin dans la base de données
    $sql = "INSERT INTO entrees_stock (nom, type_vaccin, lot_number, date_reception, date_expiration, quantite_recu, quantite_disponible) 
            VALUES (?, ?, ?, ?, ?, ?,?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssii", $nom, $type_vaccin, $lot_number, $date_reception, $date_expiration, $quantite_recu, $quantite_disponible);

        if ($stmt->execute()) {
            $success_message = "Le vaccin a été ajouté avec succès.";
        } else {
            $error_message = "Une erreur s'est produite lors de l'ajout du vaccin. " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Erreur lors de la préparation de la requête SQL. " . $conn->error;
    }
}

// Récupérer l'état du stock des vaccins
$stock_sql = "SELECT nom, type_vaccin, lot_number, date_reception, date_expiration, quantite_disponible FROM entrees_stock";
$stock_result = $conn->query($stock_sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stocks de Vaccins</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Style personnalisé */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(to right, #4e73df 0%, #224abe 100%);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.1);
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(to right, #4e73df 0%, #224abe 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            background: linear-gradient(to right, #224abe 0%, #4e73df 100%);
        }

        .alert {
            border-radius: 10px;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Carte d'ajout de vaccin -->
                <div class="card mb-4 animate__animated animate__fadeIn">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-syringe me-2"></i>Gestion des Stocks de Vaccins
                        </h2>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-clock me-1"></i>Stock en temps réel
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Formulaire avec des améliorations visuelles -->
                        <form action="vaccin.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-prescription-bottle me-2 text-primary"></i>Nom du Vaccin
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-vial"></i></span>
                                        <input type="text" class="form-control" name="nom" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tags me-2 text-primary"></i>Type de Vaccin
                                    </label>
                                    <select class="form-select" name="type_vaccin" required>
                                        <option value="">Sélectionner un type...</option>
                                        <?php
                                        $result = $conn->query("SELECT name FROM vaccine_types ORDER BY name");
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo '<option value="' . $row["name"] . '">' . $row["name"] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="lot_number" class="form-label">Numéro de lot:</label>
                                <input type="text" class="form-control" id="lot_number" name="lot_number" required>
                            </div>

                            <div class="mb-3">
                                <label for="date_reception" class="form-label">Date de Réception:</label>
                                <input type="date" class="form-control" id="date_reception" name="date_reception"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="date_expiration" class="form-label">Date d'Expiration:</label>
                                <input type="date" class="form-control" id="date_expiration" name="date_expiration"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="quantite_recu" class="form-label">Quantité reçue:</label>
                                <input type="number" class="form-control" id="quantite_recu" name="quantite_recu"
                                    required min="0">
                            </div>

                            <div class="text-end">
                                <button type="reset" class="btn btn-secondary me-2">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter le Vaccin
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau d'état du stock -->
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-warehouse me-2"></i>État du Stock
                </h3>
                <div>
                    <button class="btn btn-sm btn-outline-success btn-print">
                        <i class="fas fa-print me-1"></i>Imprimer
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <!-- En-têtes du tableau -->
                        <thead class="table-light">
                            <tr>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Lot</th>
                                <th>Réception</th>
                                <th>Expiration</th>
                                <th class="text-center">Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($stock_result->num_rows > 0) {
                                while ($row = $stock_result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['type_vaccin']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['lot_number']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['date_reception']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['date_expiration']) . '</td>';
                                    echo '<td>' . htmlspecialchars($row['quantite_disponible']) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">Aucun vaccin en stock</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printVaccineStock() {
            // Créer une nouvelle fenêtre pour l'impression
            const printWindow = window.open('', '', 'width=800,height=600');

            // Récupérer le contenu du tableau
            const tableContent = document.querySelector('.table-responsive').innerHTML;

            // Créer un document HTML personnalisé pour l'impression
            const printContent = `
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Stock de Vaccins - Impression</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 20px;
            }
            h1 {
                text-align: center;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            @media print {
                body {
                    margin: 0;
                }
            }
        </style>
    </head>
    <body>
        <h1>État du Stock de Vaccins</h1>
        <table>
            ${tableContent}
        </table>
        <p style="text-align: right; margin-top: 20px;">
            Imprimé le : ${new Date().toLocaleDateString()}
        </p>
    </body>
    </html>
    `;

            // Écrire le contenu dans la nouvelle fenêtre
            printWindow.document.write(printContent);

            // Fermer le document pour permettre l'impression
            printWindow.document.close();

            // Déclencher l'impression
            printWindow.print();
        }

        // Ajouter un écouteur d'événement au bouton d'impression
        document.querySelector('.btn-print').addEventListener('click', printVaccineStock);
    </script>

</body>

</html>