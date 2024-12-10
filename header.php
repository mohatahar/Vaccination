<?php
require_once 'auth_check.php';
$auth = AuthenticationManager::getInstance();
$auth->enforceAuthentication();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Vaccination</title>
    
    <link href="assets/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/fontawesome-free-6.7.1-web/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #3b82f6;
            --text-light: #f8fafc;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--text-light) !important;
            letter-spacing: 0.5px;
            padding: 0.5rem 1rem;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand i {
            color: var(--primary-light);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .nav-link {
            position: relative;
            padding: 0.75rem 1.25rem;
            margin: 0 0.3rem;
            color: var(--text-light) !important;
            font-weight: 500;
            transition: var(--transition);
            border-radius: 0.5rem;
            opacity: 0.9;
        }

        .nav-link:hover {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0.5rem;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: var(--text-light);
            transition: var(--transition);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 60%;
        }

        .nav-link.active {
            color: var(--text-light) !important;
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.15);
            opacity: 1;
        }

        .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem 0.75rem;
            transition: var(--transition);
        }

        .navbar-toggler:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem 0;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }

            .nav-link {
                padding: 0.75rem 1rem;
                margin: 0.2rem 0.5rem;
                border-radius: 0.375rem;
            }

            .navbar-collapse {
                background-color: var(--primary-dark);
                margin-top: 0.5rem;
                padding: 0.5rem;
                border-radius: 0.5rem;
            }

            .nav-item {
                margin: 0.3rem 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-hospital-user me-2"></i>Gestion Vaccination
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-chart-line"></i>
                            Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'ajouter_employe.php' ? 'active' : ''; ?>" href="ajouter_employe.php">
                            <i class="fas fa-user-plus"></i>
                            Ajouter Employé
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'ajouter_vaccination.php' ? 'active' : ''; ?>" href="ajouter_vaccination.php">
                            <i class="fas fa-syringe"></i>
                            Ajouter Vaccination
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'ajouter_vaccin.php' ? 'active' : ''; ?>" href="ajouter_vaccin.php">
                            <i class="fas fa-vial"></i>
                            Types vaccins
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'vaccin.php' ? 'active' : ''; ?>" href="vaccin.php">
                            <i class="fas fa-boxes"></i>
                            Stock Vaccins
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="icon-circle">
                            <i class="fas fa-user-circle"></i>
                        </span>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <li>
                                <a class="dropdown-item" href="users.php">
                                    <i class="fas fa-key me-2"></i>
                                    Gérer utilisateurs
                                </a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item" href="modifier_mot_de_passe.php">
                                <i class="fas fa-key me-2"></i>Modifier mot de passe
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap-5.3.3-dist/js/bootstrap.min.js"></script>
</body>
</html>