<?php
include 'db.php'; // Connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $recommended_doses = $_POST['recommended_doses'];

    // Préparation de la requête pour vérifier si le vaccin existe déjà (insensible à la casse)
    $check_sql = $conn->prepare("SELECT * FROM vaccine_types WHERE LOWER(name) = LOWER(?)");
    $check_sql->bind_param("s", $name);
    $check_sql->execute();
    $result = $check_sql->get_result();

    if ($result->num_rows > 0) {
        echo "Le type de vaccin existe déjà.";
    } else {
        // Requête préparée pour l'insertion
        $insert_sql = $conn->prepare("INSERT INTO vaccine_types (name, recommended_doses) VALUES (?, ?)");
        $insert_sql->bind_param("ss", $name, $recommended_doses);

        if ($insert_sql->execute()) {
            echo "Type de vaccin ajouté avec succès.";
        } else {
            echo "Erreur: " . $conn->error;
        }
    }

    $conn->close();
}
?>
