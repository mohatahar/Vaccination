<?php
session_start();
require_once 'db.php'; // Connexion MySQLi
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération des données du formulaire
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Vérification que les champs ne sont pas vides
    if (!empty($username) && !empty($password)) {
        // Connexion à la base de données avec MySQLi
        if ($conn) {
            // Préparation de la requête
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username); // "s" pour string (nom d'utilisateur)
            $stmt->execute();
            $result = $stmt->get_result();

            // Vérification si un utilisateur est trouvé
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Vérification du mot de passe avec password_verify() pour la sécurité
                if (password_verify($password, $user['password'])) {
                    // Initialisation de la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['last_activity'] = time(); // Dernière activité pour la gestion de session
                    session_regenerate_id(true); // Sécurisation de la session

                    // Redirection vers le tableau de bord
                    header('Location: dashboard.php');
                    exit;
                }
            }

            // Message d'erreur si l'identifiant ou mot de passe est incorrect
            $error = 'Identifiant ou mot de passe incorrect';
            $stmt->close();
        } else {
            // Message d'erreur si la connexion à la base de données échoue
            $error = 'Erreur de connexion à la base de données';
        }
    } else {
        // Message d'erreur si les champs sont vides
        $error = 'Veuillez remplir tous les champs';
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Connexion - Gestion de Vaccination</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --primary-hover: #45a049;
            --error-color: #ff4444;
            --text-color: #e0e0e0;
            --dark-bg: #1a1a1a;
            --card-bg: rgba(28, 28, 28, 0.8);
            --gradient-1: #0beef9;
            --gradient-2: #48ff9f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--dark-bg);
            position: relative;
            overflow: hidden;
            color: var(--text-color);
        }

        /* Animated Background */
        .background {
            position: fixed;
            width: 100vw;
            height: 100vh;
            top: 0;
            left: 0;
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            z-index: 0;
        }

        .circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            animation: animate 25s linear infinite;
            bottom: -150px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            border-radius: 50%;
        }

        .circles li:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
            animation-duration: 20s;
            border-color: var(--gradient-1);
        }

        .circles li:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
            border-color: var(--gradient-2);
        }

        .circles li:nth-child(3) {
            left: 70%;
            width: 40px;
            height: 40px;
            animation-delay: 4s;
            border-color: var(--gradient-1);
        }

        .circles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
            border-color: var(--gradient-2);
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.5;
                border-radius: 50%;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 40%;
            }
        }

        .container {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 2;
            transform-style: preserve-3d;
            perspective: 2000px;
            animation: container-float 6s ease-in-out infinite;
        }

        @keyframes container-float {
            0%, 100% {
                transform: translateY(0) rotateX(0deg) rotateY(0deg);
            }
            50% {
                transform: translateY(-10px) rotateX(2deg) rotateY(-2deg);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .logo::before {
            content: '';
            position: absolute;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, var(--gradient-1), var(--gradient-2));
            border-radius: 50%;
            filter: blur(20px);
            opacity: 0.5;
            z-index: -1;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.3;
            }
            100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
        }

        .logo i {
            font-size: 48px;
            background: linear-gradient(45deg, var(--gradient-1), var(--gradient-2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-10px) rotate(10deg);
            }
        }

        h1 {
            color: var(--text-color);
            font-size: 24px;
            margin-top: 15px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
        }

        .form-group:hover {
            transform: translateZ(20px);
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-color);
            z-index: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-color);
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        input:focus {
            border-color: var(--gradient-1);
            outline: none;
            box-shadow: 0 0 20px rgba(11, 238, 249, 0.3);
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, var(--gradient-1), var(--gradient-2));
            color: var(--dark-bg);
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to right,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(11, 238, 249, 0.4);
        }

        .error {
            background: rgba(255, 68, 68, 0.1);
            border-left: 4px solid var(--error-color);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            backdrop-filter: blur(4px);
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="background">
        <ul class="circles">
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
    </div>

    <div class="container">
        <div class="logo">
            <i class="fas fa-syringe"></i>
            <h1>Gestion de Vaccination</h1>
            <h1>EPH SOBHA</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <i class="fas fa-user"></i>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    autocomplete="username"
                    placeholder="Entrez votre nom d'utilisateur"
                >
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <i class="fas fa-lock"></i>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    autocomplete="current-password"
                    placeholder="Entrez votre mot de passe"
                >
            </div>

            <button type="submit" class="btn-login">
				<i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>
    </div>
</body>
</html>