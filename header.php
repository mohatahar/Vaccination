<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Vaccination</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            background-color: var(--primary-color) !important;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
            padding: 0.5rem 1rem;
        }

        .nav-link {
            position: relative;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        .nav-link.active {
            color: white !important;
            font-weight: 600;
        }

        .nav-link.active::after {
            width: 80%;
        }

        .nav-link i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5);
            padding: 0.5rem 0.75rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 0.75rem 1rem;
            }

            .nav-link::after {
                display: none;
            }

            .navbar-collapse {
                margin-top: 1rem;
            }

            .nav-item {
                margin: 0.2rem 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-hospital-user me-2"></i>Gestion Vaccination
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
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
                            <i class="fas fa-syringe me-1"></i>
                            Ajouter Vaccination
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'ajouter_vaccin.php' ? 'active' : ''; ?>" href="ajouter_vaccin.php">
                            <i class="fas fa-vial"></i>
                            Types vaccins
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>