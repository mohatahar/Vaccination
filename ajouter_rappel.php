<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $type_vaccin = $_POST['type_vaccin'];
    $prochain_rappel = $_POST['prochain_rappel'];

    $sql = "INSERT INTO vaccinations (employee_id, type_vaccin, prochain_rappel) 
            VALUES ('$employee_id', '$type_vaccin', '$prochain_rappel')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Rappel ajouté avec succès!";
        header('Location: dashboard.php');
    } else {
        echo "Erreur: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
}
?>
