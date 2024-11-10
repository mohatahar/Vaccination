<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connexion à la base de données
    include 'db.php'; // Assurez-vous que le fichier db_connection.php gère la connexion

    // Récupération des données du formulaire
    $employee_id = $_POST['employee_id'];
    $type_vaccin_id = $_POST['type_vaccin'];
    $reminder_date = $_POST['reminder_date'];

    // Préparation de l'insertion dans la base de données
    $sql = "INSERT INTO vaccine_reminders (employee_id, type_vaccin, reminder_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $employee_id, $type_vaccin_id, $reminder_date);

    // Exécution de la requête
    if ($stmt->execute()) {
        echo "Rappel de vaccin ajouté avec succès";
    } else {
        echo "Erreur: " . $stmt->error;
    }

    // Fermeture de la connexion
    $stmt->close();
    $conn->close();
}
?>
