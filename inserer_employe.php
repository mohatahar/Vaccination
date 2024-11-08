<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];
    $grade = $_POST['grade'];
    $service = $_POST['service'];
	$telephone = $_POST['telephone'];

    // Vérifier si l'employé existe déjà (basé sur nom, prénom et date de naissance)
    $sql_check = "SELECT * FROM employees WHERE nom = ? AND prenom = ? AND date_naissance = ?";
    
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("sss", $nom, $prenom, $date_naissance);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            // Si l'employé existe déjà, renvoyer un message spécifique
            echo "employee_exists";
        } else {
            // L'employé n'existe pas, on peut l'ajouter
            $sql = "INSERT INTO employees (nom, prenom, sexe, date_naissance, grade, service, telephone) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssssss", $nom, $prenom, $sexe, $date_naissance, $grade, $service, $telephone);
                
                if ($stmt->execute()) {
                    echo "success";
                } else {
                    echo "error";
                }
                
                $stmt->close();
            } else {
                echo "error";
            }
        }

        $stmt_check->close();
    } else {
        echo "error";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout Employé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin-top: 50px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        .message {
            font-size: 18px;
            margin-bottom: 20px;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Affichage du message de confirmation ou d'erreur -->
        <?php if (!empty($confirmation_message)): ?>
            <div class="message <?php echo strpos($confirmation_message, 'Erreur') !== false ? 'error' : ''; ?>">
                <?php echo $confirmation_message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
