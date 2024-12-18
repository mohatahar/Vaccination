<?php
include 'db.php';
include 'header.php';

// Traitement du formulaire pour ajouter un vaccin (entrée en stock)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'entree') {
    // [Previous input stock code remains the same]
    $nom = $_POST['nom'];
    $type_vaccin = $_POST['type_vaccin'];
    $lot_number = $_POST['lot_number'];
    $type_flacon = $_POST['type_flacon'];
    $date_reception = $_POST['date_reception'];
    $date_expiration = $_POST['date_expiration'];
    $quantite_recu = $_POST['quantite_recu'];
    $quantite_disponible = $_POST['quantite_recu'];

    $sql = "INSERT INTO entrees_stock (nom, type_vaccin, lot_number, type_flacon, date_reception, date_expiration, quantite_recu, quantite_disponible) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssssssii", $nom, $type_vaccin, $lot_number, $type_flacon, $date_reception, $date_expiration, $quantite_recu, $quantite_disponible);

    if ($stmt->execute()) {
        $success_message = "Le vaccin a été ajouté avec succès.";
    } else {
        // Detailed error logging
        $error_message = "Erreur lors de l'ajout du vaccin: " . $stmt->error;
        error_log("MySQL Error: " . $stmt->error);
    }
    $stmt->close();
} else {
    $error_message = "Erreur lors de la préparation de la requête SQL: " . $conn->error;
    error_log("MySQL Prepare Error: " . $conn->error);
}
}

// Traitement du formulaire pour la sortie de stock
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'sortie') {
    $lot_number = $_POST['lot_number'];
    $quantite_sortie = $_POST['quantite_sortie'];
    $date_sortie = $_POST['date_sortie'];
    $utilisateur = $_SESSION['username'];

    // Vérifier la quantité disponible
    $check_sql = "SELECT quantite_disponible FROM entrees_stock WHERE lot_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $lot_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $stock_row = $check_result->fetch_assoc();

    if ($stock_row['quantite_disponible'] >= $quantite_sortie) {
        // Mettre à jour la quantité disponible
        $update_sql = "UPDATE entrees_stock SET quantite_disponible = quantite_disponible - ? WHERE lot_number = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("is", $quantite_sortie, $lot_number);

        // Insérer l'enregistrement de sortie
        $insert_sql = "INSERT INTO sorties_stock (lot_number, quantite_sortie, date_sortie, utilisateur) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("siss", $lot_number, $quantite_sortie, $date_sortie, $utilisateur);
        if ($update_stmt->execute() && $insert_stmt->execute()) {
            $success_message = "Sortie de stock effectuée avec succès.";
        } else {
            $error_message = "Une erreur s'est produite lors de la sortie de stock.";
        }

        $update_stmt->close();
        $insert_stmt->close();
    } else {
        $error_message = "Quantité insuffisante en stock.";
    }
    $check_stmt->close();
}

// Récupérer l'état du stock des vaccins
$stock_sql = "SELECT nom, type_vaccin, lot_number, date_reception, date_expiration, quantite_disponible FROM entrees_stock";
$stock_result = $conn->query($stock_sql);

// Récupérer l'historique des sorties de stock
$sorties_sql = "SELECT s.lot_number, e.nom, e.type_vaccin, s.quantite_sortie, s.date_sortie, s.utilisateur
                FROM sorties_stock s
                JOIN entrees_stock e ON s.lot_number = e.lot_number
                ORDER BY s.date_sortie DESC";
$sorties_result = $conn->query($sorties_sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stocks de Vaccins</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/css2.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
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
    <div class="page-header text-center">
        <div class="container">
            <h1><i class="fas fa-boxes"></i> Gestion de Stock Vaccins</h1>
            <p class="lead">Ajoutez et gérez les mouvements de stock</p>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Carte d'ajout de vaccin -->
                <div class="card mb-5 fade-in">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-arrow-down text-success me-2"></i>Nouvelle Entrée en Stock
                        </h2>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-clock me-1"></i>Stock en temps réel
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Formulaire avec des améliorations visuelles -->
                        <form action="vaccin.php" method="POST">
                        <input type="hidden" name="action" value="entree">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-prescription-bottle me-2 text-primary"></i>Nom commercial du
                                        Vaccin
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

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="lot_number" class="form-label">Numéro de lot:</label>
                                    <input type="text" class="form-control" id="lot_number" name="lot_number" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type de Flacon</label>
                                    <select class="form-select" name="type_flacon" required>
                                        <option value="unidose">Unidose</option>
                                        <option value="multidose">Multidose</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="date_reception" class="form-label">Date de Réception</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" class="form-control" id="date_reception" name="date_reception"
                                        required placeholder="Sélectionnez la date de réception">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="date_expiration" class="form-label">Date d'Expiration</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" class="form-control" id="date_expiration" name="date_expiration"
                                        required placeholder="Sélectionnez la date d'expiration">
                                </div>
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


        <!-- Carte de sortie de stock -->
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card mb-5 fade-in">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-arrow-up text-danger me-2"></i>Nouvelle Sortie de Stock
                        </h2>
                    </div>
                    <div class="card-body">
                        <form action="vaccin.php" method="POST">
                            <input type="hidden" name="action" value="sortie">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Numéro de Lot</label>
                                    <select class="form-select" name="lot_number" required>
                                        <option value="">Sélectionner un lot...</option>
                                        <?php
                                        // Récupérer les lots avec stock disponible
                                        $lots_sql = "SELECT lot_number, nom, type_vaccin, quantite_disponible 
                                                    FROM entrees_stock 
                                                    WHERE quantite_disponible > 0 
                                                    ORDER BY date_expiration ASC";
                                        $lots_result = $conn->query($lots_sql);
                                        if ($lots_result->num_rows > 0) {
                                            while ($lot = $lots_result->fetch_assoc()) {
                                                echo '<option value="' . htmlspecialchars($lot['lot_number']) . '">';
                                                echo htmlspecialchars($lot['nom']) . ' - ' . htmlspecialchars($lot['type_vaccin']) .
                                                    ' (Lot: ' . htmlspecialchars($lot['lot_number']) .
                                                    ', Dispo: ' . htmlspecialchars($lot['quantite_disponible']) . ')';
                                                echo '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quantité à Sortir</label>
                                    <input type="number" class="form-control" name="quantite_sortie" required min="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_sortie" class="form-label">Date de Sortie</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" class="form-control" id="date_sortie" name="date_sortie"
                                            required placeholder="Sélectionnez la date de sortie">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Effectuer la Sortie
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Tableau d'état du stock -->
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card mb-5 fade-in">
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
                                        <th>Stock Actuel</th>                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($stock_result->num_rows > 0) {
                                        while ($row = $stock_result->fetch_assoc()) {
                                            // Vérifier si la quantité disponible est supérieure à 0
                                            if ($row['quantite_disponible'] > 0) {
                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['type_vaccin']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['lot_number']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['date_reception']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['date_expiration']) . '</td>';
                                                echo '<td>' . htmlspecialchars($row['quantite_disponible']) . '</td>';
                                                echo '</tr>';
                                            }
                                        }

                                        // Ajouter un message si aucun stock n'est disponible après filtrage
                                        if ($stock_result->num_rows === 0) {
                                            echo '<tr><td colspan="7" class="text-center">Aucun vaccin en stock</td></tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">Aucun vaccin en stock</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des Entrées -->
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card mb-5 fade-in">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-arrow-down text-success me-2"></i>Entrées de Stock
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Lot</th>
                                        <th>Date Entrée</th>
                                        <th>Quantité Entrée</th>
                                        <th>Date Expiration</th>
                                        <th>Stock Actuel</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupérer les entrées de stock, triées par date
                                    $entries_sql = "
                        SELECT 
                            nom, 
                            type_vaccin, 
                            lot_number, 
                            date_reception, 
                            quantite_recu, 
                            date_expiration, 
                            quantite_disponible
                        FROM entrees_stock
                        ORDER BY date_reception DESC
                    ";
                                    $entries_result = $conn->query($entries_sql);

                                    if ($entries_result->num_rows > 0) {
                                        while ($row = $entries_result->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['type_vaccin']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['lot_number']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['date_reception']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['quantite_recu']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['date_expiration']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['quantite_disponible']) . '</td>';
                                            echo '<td>';
                                            echo '<a href="modifier_stock.php?lot_number=' . urlencode($row['lot_number']) . '" class="btn btn-warning btn-sm mr-2">Modifier</a>';
                                            echo '<a href="supprimer_stock.php?lot_number=' . urlencode($row['lot_number']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce stock de vaccin ?\')">Supprimer</a>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">Aucune entrée de stock</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des Sorties -->
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card mb-5 fade-in">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-arrow-up text-danger me-2"></i>Sorties de Stock
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Lot</th>
                                        <th>Date Sortie</th>
                                        <th>Quantité Sortie</th>
                                        <th>Utilisateur</th>
                                        <th>Stock Actuel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Récupérer les sorties de stock, triées par date
                                    $exits_sql = "
                        SELECT 
                            e.nom, 
                            e.type_vaccin, 
                            e.lot_number,
                            s.date_sortie,
                            s.quantite_sortie,
                            s.utilisateur,
                            e.quantite_disponible
                        FROM sorties_stock s
                        JOIN entrees_stock e ON s.lot_number = e.lot_number
                        ORDER BY s.date_sortie DESC
                    ";
                                    $exits_result = $conn->query($exits_sql);

                                    if ($exits_result->num_rows > 0) {
                                        while ($row = $exits_result->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['type_vaccin']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['lot_number']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['date_sortie']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['quantite_sortie']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['utilisateur']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['quantite_disponible']) . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">Aucune sortie de stock</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/flatpickr/flatpickr.min.css">
    <script src="assets/flatpickr/flatpickr.js"></script>
    <script src="assets/flatpickr/fr.js"></script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configuration pour la date de réception
            flatpickr("#date_reception", {
                locale: "fr", // Langue française
                dateFormat: "Y-m-d", // Format de date pour PHP
                altInput: true, // Crée un input alternatif lisible
                altFormat: "d/m/Y", // Format lisible pour l'utilisateur
                maxDate: "today", // Ne permet pas de sélectionner des dates futures
                allowInput: true, // Permet la saisie manuelle
                disableMobile: true // Utilise le sélecteur natif sur mobile
            });

            // Configuration pour la date d'expiration
            flatpickr("#date_expiration", {
                locale: "fr",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                minDate: "today", // Date d'expiration doit être dans le futur
                allowInput: true,
                disableMobile: true
            });

            // Configuration pour la date de sortie
            flatpickr("#date_sortie", {
                locale: "fr", // Langue française
                dateFormat: "Y-m-d", // Format de date pour PHP
                altInput: true, // Crée un input alternatif lisible
                altFormat: "d/m/Y", // Format lisible pour l'utilisateur
                allowInput: true, // Permet la saisie manuelle
                disableMobile: true // Utilise le sélecteur natif sur mobile
            });
        });
    </script>
</body>

</html>

<?php include 'footer.php'; ?>