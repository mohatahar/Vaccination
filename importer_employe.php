<?php
require 'vendor/autoload.php';  // Assurez-vous que l'autoloader de Composer est inclus
include 'header.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_vaccination";

// Connexion à la base de données MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifiez si un fichier a été téléchargé
if (isset($_FILES['excel_file'])) {
    // Récupérer le fichier téléchargé
    $file = $_FILES['excel_file']['tmp_name'];

    // Charger le fichier Excel avec PhpSpreadsheet
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Lire les données de la première feuille (sheet)
    $data = $sheet->toArray();

    // Parcourir les lignes du fichier Excel
    foreach ($data as $row) {
        // Supposons que les colonnes sont : nom, prénom
        $nom = $row[0];
        $prenom = $row[1];
        $sexe = $row[2];
        $date_naissance = $row[3];
        $grade = $row[4];
        $service = $row[5];
        $telephone = $row[6];

        $date_obj = DateTime::createFromFormat('d/m/Y', $date_naissance);
        $formatted_date = $date_obj->format('Y-m-d');

        // Préparer la requête SQL d'insertion
        $sql = "INSERT INTO employees (nom, prenom, sexe, date_naissance, grade, service, telephone) VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Préparer la requête
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $nom, $prenom, $sexe, $formatted_date, $grade, $service, $telephone);  // Bind les paramètres

        // Exécuter la requête
        if ($stmt->execute()) {
            echo "Données insérées avec succès.<br>";
        } else {
            echo "Erreur d'insertion: " . $stmt->error . "<br>";
        }

        // Fermer la requête préparée
        $stmt->close();
    }

    // Fermer la connexion à la base de données
    $conn->close();
} else {
    echo "Aucun fichier n'a été téléchargé.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importation des Employés</title>
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f4f6f9;
            --text-color: #333;
            --white: #ffffff;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .import-container {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .import-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        .file-input-wrapper {
            position: relative;
            margin-bottom: 25px;
        }

        .btn-file-upload {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .btn-file-upload:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-file-upload i {
            font-size: 20px;
        }

        input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-name {
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        .submit-btn {
            background-color: var(--secondary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        .submit-btn i {
            font-size: 18px;
        }

        @media (max-width: 480px) {
            .import-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>


    <main class="main-content">
        <div class="import-container">
            <form action="importer_employe.php" method="POST" enctype="multipart/form-data">
                <div class="file-input-wrapper">
                    <button type="button" class="btn-file-upload" onclick="document.getElementById('excel_file').click()">
                        <i class="fas fa-file-upload"></i> Sélectionner un fichier Excel
                    </button>
                    <input type="file"
                        name="excel_file"
                        id="excel_file"
                        accept=".xlsx,.xls,.csv"
                        required
                        onchange="updateFileName(this)">
                </div>
                <div id="file-chosen" class="file-name">Aucun fichier sélectionné</div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-check-circle"></i> Importer la liste d'employés
                </button>
            </form>
        </div>
    </main>
    
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('file-chosen');
            if (input.files && input.files.length > 0) {
                fileNameDisplay.textContent = input.files[0].name;
                fileNameDisplay.style.color = '#333';
            } else {
                fileNameDisplay.textContent = 'Aucun fichier sélectionné';
                fileNameDisplay.style.color = '#666';
            }
        }
    </script>
</body>
</html>

<?php include 'footer.php'; ?>