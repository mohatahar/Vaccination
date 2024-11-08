<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des employés et vaccinations</title>
</head>
<body>
    <h2>Liste des employés</h2>
    <table border="1">
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Vaccination</th>
            <th>Type de Vaccin</th>
            <th>Date de Vaccination</th>
            <th>Dose</th>
            <th>Prochain Rappel</th>
        </tr>
        <?php
        $sql = "SELECT employees.nom, employees.prenom, vaccinations.type_vaccin, vaccinations.date_vaccination, 
                vaccinations.dose, vaccinations.prochain_rappel
                FROM employees
                LEFT JOIN vaccinations ON employees.id = vaccinations.employee_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['nom'] . "</td>
                        <td>" . $row['prenom'] . "</td>
                        <td>" . ($row['type_vaccin'] ? 'Oui' : 'Non') . "</td>
                        <td>" . $row['type_vaccin'] . "</td>
                        <td>" . $row['date_vaccination'] . "</td>
                        <td>" . $row['dose'] . "</td>
                        <td>" . $row['prochain_rappel'] . "</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Aucun employé trouvé</td></tr>";
        }

        $conn->close();
        ?>
    </table>
</body>
</html>
