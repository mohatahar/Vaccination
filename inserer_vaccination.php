<?php
include 'db.php'; // Inclusion de la connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des valeurs du formulaire
    $employee_id = $_POST['employee_id'];
    $type_vaccin_id = $_POST['type_vaccin'];
    $lot_number = $_POST['lot_number'];
    $date_vaccination = $_POST['date_vaccination'];
    $dose = $_POST['dose'];
    $prochain_rappel = $_POST['prochain_rappel'];
    $rappel_effectue = "oui";
    
    // Récupérer les informations du type de vaccin
    $sql = "SELECT name, recommended_doses FROM vaccine_types WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $type_vaccin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $type_vaccin_name = $row['name'];
        $recommended_doses = $row['recommended_doses'];

        if ($dose < $recommended_doses) {
            $rappel_effectue = "non";
        }

        // Insertion dans la table vaccinations
        $sql_insert = "INSERT INTO vaccinations (employee_id, type_vaccin, lot_number, date_vaccination, dose, prochain_rappel, rappel_effectue)
                       VALUES (?, ?, ?, ?, ?, ?,?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issssss", $employee_id, $type_vaccin_name, $lot_number, $date_vaccination, $dose, $prochain_rappel, $rappel_effectue);

        // Exécution de la requête et gestion des erreurs
        if ($stmt_insert->execute()) {
            echo "Vaccination ajoutée avec succès";
        } else {
            echo "Erreur: " . $stmt_insert->error;
        }

        $stmt_insert->close();
    } else {
        echo "Erreur : Type de vaccin introuvable";
    }

    // Fermeture des requêtes et de la connexion
    $stmt->close();
    $conn->close();
}
?>