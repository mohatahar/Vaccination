<?php include 'db.php';

// Récupérer l'année en cours
$currentYear = date("Y");
// Requête pour obtenir le pourcentage de vaccination par type de vaccin
$sql_vaccine_stats = "
    SELECT 
        v.type_vaccin,
        COUNT(DISTINCT v.employee_id) as vaccinated_count,
        (SELECT COUNT(*) FROM employees e WHERE YEAR(v.date_vaccination) = $currentYear) as total_employees,
        ROUND(COUNT(DISTINCT v.employee_id) * 100.0 / 
            (SELECT COUNT(*) FROM employees e WHERE YEAR(v.date_vaccination) = $currentYear), 2) as percentage
    FROM vaccinations v
    WHERE YEAR(v.date_vaccination) = $currentYear
    GROUP BY v.type_vaccin
    ORDER BY vaccinated_count DESC";
$result_vaccine_stats = $conn->query($sql_vaccine_stats);

// Requête pour obtenir l'historique détaillé des vaccinations par employé
$sql_employee_history = "
    SELECT 
        e.id,
        e.nom,
        e.prenom,
		e.grade,
        e.service,
        GROUP_CONCAT(
            CONCAT(
                v.type_vaccin, 
                ' (Dose ', v.dose, 
                ' - Date: ', DATE_FORMAT(v.date_vaccination, '%d/%m/%Y'),
                CASE 
                    WHEN v.prochain_rappel IS NOT NULL AND v.rappel_effectue = 'non'
                    THEN CONCAT(' - Prochain rappel: ', DATE_FORMAT(v.prochain_rappel, '%d/%m/%Y'))
                    ELSE ''
                END
            )
            ORDER BY v.date_vaccination ASC
            SEPARATOR '|'
        ) as vaccination_history
    FROM employees e
    LEFT JOIN vaccinations v ON e.id = v.employee_id
    GROUP BY e.id
";
$result_employee_history = $conn->query($sql_employee_history);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord - Vaccination du personnel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style1.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-hospital-user me-2"></i>
                Gestion Vaccination - EPH SOBHA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-chart-line me-1"></i>Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ajouter_employe.php">
                            <i class="fas fa-user-plus me-1"></i>Ajouter Employé
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ajouter_vaccination.php">
                            <i class="fas fa-syringe me-1"></i>Nouvelle Vaccination
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ajouter_vaccin.php">
                            <i class="fas fa-vial me-1"></i>Types vaccins
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div class="page-header text-center">
            <div class="container">
                <h1><i class="fas fa-chart-line me-2"></i>Tableau de bord des vaccinations - EPH SOBHA</h1>
                <p class="lead"> </p>
            </div>
        </div>

        <div class="row">

            <!-- Card Total Employés -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users stat-icon text-primary"></i>
                        <h5 class="card-title fw-bold">Total Employés</h5>
                        <p class="card-text display-4 fw-bold text-primary">
                            <?php
                            $sql = "SELECT COUNT(*) AS total FROM employees";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'];
                            ?>
                        </p>

                        <?php
                        // Requête pour les statistiques globales
                        $sql = "SELECT 
                            COUNT(*) AS total,
                            SUM(CASE WHEN sexe = 'M' THEN 1 ELSE 0 END) AS hommes,
                            SUM(CASE WHEN sexe = 'F' THEN 1 ELSE 0 END) AS femmes
                            FROM employees";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();

                        $hommes = $row['hommes'];
                        $femmes = $row['femmes'];
                        ?>

                        <!-- Statistiques rapides -->
                        <div class="quick-stats">
                            <div class="stat-item d-flex justify-content-between align-items-center mb-2">
                                <span><i class="fas fa-male text-info me-2"></i>Hommes</span>
                                <strong><?php echo $hommes; ?></strong>
                            </div>
                            <div class="stat-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-female text-danger me-2"></i>Femmes</span>
                                <strong><?php echo $femmes; ?></strong>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#employeeListModal">
                                <i class="fas fa-list me-2"></i>Voir la liste
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Employés Vaccinés -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stats-icon-wrapper me-3">
                                <i class="fas fa-syringe fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-0 fw-bold">Employés vaccinés</h5>
                        </div>

                        <?php
                        // Requête pour les statistiques globales
                        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN id IN (SELECT DISTINCT employee_id FROM vaccinations) THEN 1 ELSE 0 END) AS vaccinated
                    FROM employees";
                        $result = $conn->query($sql);
                        $row = $result->fetch_assoc();

                        $total = $row['total'];
                        $vaccinated = $row['vaccinated'];
                        $percentage = ($total > 0) ? round(($vaccinated / $total) * 100, 2) : 0;

                        ?>

                        <!-- Taux de vaccination avec jauge circulaire -->
                        <div class="text-center mb-3">
                            <div class="position-relative d-inline-block">
                                <svg width="120" height="120" viewBox="0 0 120 120">
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="#e9ecef" stroke-width="12" />
                                    <circle cx="60" cy="60" r="54" fill="none" stroke="#0d6efd" stroke-width="12"
                                        stroke-dasharray="339.292"
                                        stroke-dashoffset="<?php echo 339.292 * (1 - $percentage / 100); ?>"
                                        transform="rotate(-90 60 60)" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <h3 class="mb-0 fw-bold"><?php echo $percentage; ?>%</h3>
                                    <small class="text-muted">Vaccinés</small>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques rapides -->
                        <div class="quick-stats">
                            <div class="stat-item d-flex justify-content-between align-items-center mb-2">
                                <span><i class="fas fa-users-medical text-primary me-2"></i>Nombre </span>
                                <strong><?php echo number_format($vaccinated); ?></strong>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Bouton pour plus de statistiques -->
                        <div class="d-grid">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#statisticsModal">
                                <i class="fas fa-chart-bar me-2"></i>Statistiques détaillées
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // Ajouter des délais d'animation aux éléments stat-item
                        document.querySelectorAll('.stat-item').forEach((item, index) => {
                            item.style.setProperty('--item-index', index);
                        });

                        // Animation de la jauge circulaire au chargement
                        const circle = document.querySelector('circle:last-child');
                        circle.style.strokeDashoffset = '339.292';
                        setTimeout(() => {
                            circle.style.strokeDashoffset = '<?php echo 339.292 * (1 - $percentage / 100); ?>';
                        }, 300);
                    });
                </script>
            </div>


            <!-- Card Employés Non Vaccinés -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="glass-card h-100">
                    <?php
                    $sql = "SELECT 
						COUNT(*) AS non_vaccines,
						(SELECT COUNT(*) FROM employees) as total_employees
						FROM employees 
						WHERE id NOT IN (SELECT DISTINCT employee_id FROM vaccinations)";

                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();
                    $percentage = round(($row['non_vaccines'] / $row['total_employees']) * 100, 1);
                    ?>

                    <div class="glass-card-body">
                        <!-- En-tête de la carte -->
                        <div class="status-header">
                            <div class="status-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="status-badge">
                                <h5 class="card-title mb-0 fw-bold">Employés Non Vaccinés</h5>
                            </div>
                        </div>

                        <!-- Contenu principal -->
                        <div class="status-content">
                            <div class="main-stat">
                                <h3><?php echo $row['non_vaccines']; ?></h3>
                                <p>Non vaccinés</p>
                            </div>

                            <!-- Barre de progression circulaire -->
                            <div class="progress-ring-container">
                                <div class="progress-ring">
                                    <div class="progress-circle" style="--percentage: <?php echo $percentage; ?>">
                                        <span class="progress-value"><?php echo $percentage; ?>%</span>
                                    </div>
                                </div>
                                <span class="progress-label">du personnel</span>
                            </div>
                        </div>

                        <hr class="my-3">

                        <!-- Boutons d'action -->
                        <div class="action-buttons">
                            <button class="btn-glass-primary" data-bs-toggle="modal"
                                data-bs-target="#nonVaccinatedModal">
                                <i class="fas fa-list-ul"></i>
                                <span>Voir détails</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte des Rappels à venir -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card position-relative h-100 bg-white">
                    <!-- Gradient de fond subtil -->
                    <div class="card-gradient"></div>

                    <div class="card-body p-4">
                        <!-- En-tête avec design moderne -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-wrapper">
                                    <i class="fas fa-bell "></i>
                                </div>
                                <h5 class="card-title fw-bold mb-0">Rappels à venir</h5>
                            </div>
                            <?php
                            // Initialisation des variables
                            $urgent_count = 0;
                            $missed_count = 0;
                            $stats = [
                                'manques' => 0,
                                'cette_semaine' => 0,
                                'ce_mois' => 0,
                                'total' => 0
                            ];

                            try {
                                if (!$conn || $conn->connect_error) {
                                    throw new Exception("Erreur de connexion à la base de données");
                                }

                                // Requête pour les rappels urgents ET manqués
                                $urgent_sql = "SELECT 
                                    SUM(CASE WHEN prochain_rappel <= CURDATE() THEN 1 ELSE 0 END) as missed_count,
                                    SUM(CASE WHEN prochain_rappel > CURDATE() AND prochain_rappel <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as urgent_count
                                 FROM vaccinations 
                                 WHERE rappel_effectue = 'non'";

                                $urgent_result = $conn->query($urgent_sql);

                                if ($urgent_result === false) {
                                    throw new Exception("Erreur lors de la requête des rappels urgents: " . $conn->error);
                                }

                                $urgency_data = $urgent_result->fetch_assoc();
                                $urgent_count = $urgency_data['urgent_count'];
                                $missed_count = $urgency_data['missed_count'];

                                // Affichage des badges d'alerte
                                if ($missed_count > 0) {
                                    echo '<div class="d-flex gap-2">';
                                    echo '<div class="badge-pulse-critical">
                                <span class="badge bg-critical-soft text-critical rounded-pill px-3">
                                    ' . htmlspecialchars($missed_count) . ' manqués
                                </span>
                              </div>';
                                    if ($urgent_count > 0) {
                                        echo '<div class="badge-pulse">
                                    <span class="badge bg-danger-soft text-danger rounded-pill px-3">
                                        ' . htmlspecialchars($urgent_count) . ' urgents
                                    </span>
                                  </div>';
                                    }
                                    echo '</div>';
                                } elseif ($urgent_count > 0) {
                                    echo '<div class="badge-pulse">
                                <span class="badge bg-danger-soft text-danger rounded-pill px-3">
                                    ' . htmlspecialchars($urgent_count) . ' urgents
                                </span>
                              </div>';
                                }

                                // Requête principale mise à jour
                                $main_sql = "SELECT 
                        SUM(CASE 
                            WHEN prochain_rappel <= CURDATE() THEN 1 
                            ELSE 0 
                        END) as manques,
                        SUM(CASE 
                            WHEN prochain_rappel > CURDATE() 
                            AND prochain_rappel <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 
                            ELSE 0 
                        END) as cette_semaine,
                        SUM(CASE 
                            WHEN prochain_rappel > DATE_ADD(CURDATE(), INTERVAL 7 DAY) 
                            AND prochain_rappel <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 
                            ELSE 0 
                        END) as ce_mois,
                        COUNT(*) AS total
                        FROM vaccinations 
                        WHERE rappel_effectue = 'non'";

                                $result = $conn->query($main_sql);

                                if ($result === false) {
                                    throw new Exception("Erreur lors de la requête principale: " . $conn->error);
                                }

                                $stats = $result->fetch_assoc();
                                $stats['manques'] = $stats['manques'] ?? 0;
                                $stats['cette_semaine'] = $stats['cette_semaine'] ?? 0;
                                $stats['ce_mois'] = $stats['ce_mois'] ?? 0;
                                $stats['total'] = $stats['total'] ?? 0;

                            } catch (Exception $e) {
                                error_log("Erreur dans la carte des rappels: " . $e->getMessage());
                            }
                            ?>
                        </div>

                        <!-- Statistiques avec design moderne -->
                        <div class="stats-container">
                            <!-- Compteur principal avec effet de surbrillance -->
                            <div class="main-counter mb-4">
                                <div class="glow-effect"></div>
                                <h2 class="display-4 fw-bold mb-0">
                                    <?php echo htmlspecialchars($stats['total']); ?>
                                </h2>
                                <span class="text-secondary fs-6">Rappels en attente</span>
                            </div>

                            <!-- Détails des périodes avec barres de progression -->
                            <div class="period-details">
                                <?php if ($stats['manques'] > 0): ?>
                                    <div class="period-item mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-critical fw-medium">Rappels manqués</span>
                                            <span class="badge bg-critical-soft text-critical rounded-pill px-3">
                                                <?php echo htmlspecialchars($stats['manques']); ?>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-critical" role="progressbar"
                                                style="width: <?php echo ($stats['total'] > 0) ? ($stats['manques'] / $stats['total'] * 100) : 0; ?>%"
                                                aria-valuenow="<?php echo $stats['manques']; ?>" aria-valuemin="0"
                                                aria-valuemax="<?php echo $stats['total']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="period-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-danger fw-medium">Cette semaine</span>
                                        <span class="badge bg-danger-soft text-danger rounded-pill px-3">
                                            <?php echo htmlspecialchars($stats['cette_semaine']); ?>
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-danger" role="progressbar"
                                            style="width: <?php echo ($stats['total'] > 0) ? ($stats['cette_semaine'] / $stats['total'] * 100) : 0; ?>%"
                                            aria-valuenow="<?php echo $stats['cette_semaine']; ?>" aria-valuemin="0"
                                            aria-valuemax="<?php echo $stats['total']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="period-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-warning fw-medium">Ce mois</span>
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3">
                                            <?php echo htmlspecialchars($stats['ce_mois']); ?>
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                            style="width: <?php echo ($stats['total'] > 0) ? ($stats['ce_mois'] / $stats['total'] * 100) : 0; ?>%"
                                            aria-valuenow="<?php echo $stats['ce_mois']; ?>" aria-valuemin="0"
                                            aria-valuemax="<?php echo $stats['total']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Éléments décoratifs modernisés -->
                        <div class="decoration-circles">
                            <div class="circle circle-1"></div>
                            <div class="circle circle-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Pourcentage de vaccination -->
        <div class="row mt-4">
            <!-- Statistiques par type de vaccin -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Pourcentage de vaccination par type pour l'année <span
                            id="currentYear"></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while ($row = $result_vaccine_stats->fetch_assoc()): ?>
                            <div class="col-md-4 mb-3">
                                <div class="vaccine-stat-card border rounded p-3">
                                    <h6 class="text-primary"><?php echo htmlspecialchars($row['type_vaccin']); ?></h6>
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: <?php echo $row['percentage']; ?>%"
                                            aria-valuenow="<?php echo $row['percentage']; ?>" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <?php echo $row['percentage']; ?>%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo $row['vaccinated_count']; ?> employés sur
                                        <?php echo $row['total_employees']; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Historique des vaccinations par employé -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-4">
                                    <i class="fas fa-history me-2"></i>Historique des vaccinations
                                </h5>
                                <div class="input-group" style="width: 300px;">
                                    <input type="text" class="form-control" id="searchInput"
                                        placeholder="Rechercher...">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>


                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Employé</th>
                                                <th>Grade</th>
                                                <th>Service</th>
                                                <th>Statut</th>
                                                <th>Historique des vaccinations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $result_employee_history->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['grade']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['service']); ?></td>
                                                    <td>
                                                        <?php if ($row['vaccination_history']): ?>
                                                            <span class="badge bg-success">Vacciné</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Non vacciné</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($row['vaccination_history']): ?>
                                                            <ul class="list-unstyled mb-0">
                                                                <?php foreach (explode('|', $row['vaccination_history']) as $vaccination): ?>
                                                                    <li>
                                                                        <i class="fas fa-syringe me-1 text-primary"></i>
                                                                        <?php echo htmlspecialchars($vaccination); ?>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <em class="text-muted">Aucune vaccination enregistrée</em>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Tableau des prochains rappels -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-clock me-2"></i>Prochains rappels
                            </h5>

                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="searchReminders"
                                    placeholder="Rechercher...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Type de vaccin</th>
                                        <th>Prochaine dose</th>
                                        <th>Date du rappel</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Requête SQL pour exclure les enregistrements où 'prochain_rappel' est NULL
                                    $sql = "SELECT e.id, e.nom, e.prenom, v.id AS vaccination_id, v.type_vaccin, v.dose, v.prochain_rappel 
                                    FROM employees e 
                                    JOIN vaccinations v ON e.id = v.employee_id 
                                    WHERE v.prochain_rappel IS NOT NULL AND v.rappel_effectue != 'oui'
                                    ORDER BY v.prochain_rappel ASC";
                                    $result = $conn->query($sql);

                                    // Date actuelle
                                    $current_date = date('Y-m-d');
                                    // Définir la date de rappel imminent (3 jours à l'avance)
                                    $warning_date = date('Y-m-d', strtotime('+3 days'));
                                    $i = 1; // Initialiser le compteur ici
                                    // Boucle pour afficher les résultats
                                    while ($row = $result->fetch_assoc()) {
                                        $adjusted_dose = $row['dose'] + 1;  // Ajouter 1 à la dose
                                    
                                        // Formatage de la date en d/m/Y
                                        $formatted_rappel_date = date('d/m/Y', strtotime($row['prochain_rappel']));

                                        // Vérifier si la date du rappel est dépassée ou imminente
                                        $row_class = '';
                                        $status_message = '';

                                        if ($row['prochain_rappel'] < $current_date) {
                                            $row_class = 'table-danger';  // Rouge pour rappels dépassés
                                            $status_message = '<span class="badge bg-danger">Rappel manqué</span>';
                                        } elseif ($row['prochain_rappel'] <= $warning_date) {
                                            $row_class = 'table-warning';  // Jaune pour rappels imminents
                                            $status_message = '<span class="badge bg-warning text-dark">Rappel imminent</span>';
                                        } else {
                                            $status_message = '<span class="badge bg-success">À jour</span>';
                                        }

                                        echo "<tr class='{$row_class}'>
										<td>{$i}</td> <!-- Affiche l'index -->
                                        <td>{$row['nom']}</td>
                                        <td>{$row['prenom']}</td>
                                        <td>{$row['type_vaccin']}</td>
                                        <td>{$adjusted_dose}</td>
                                        <td>{$formatted_rappel_date}</td>
                                        <td>{$status_message}</td>
                                      </tr>";
                                        $i++; // Incrémentation du compteur après l'affichage
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal pour les statistiques détaillées -->
        <div class="modal fade" id="statisticsModal" tabindex="-1" aria-labelledby="statisticsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statisticsModalLabel">Statistiques détaillées</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Taux de vaccination par service</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Taux de vaccination</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT e.service, 
                                                   COUNT(*) AS total,
                                                   SUM(CASE WHEN e.id IN (SELECT DISTINCT employee_id FROM vaccinations) THEN 1 ELSE 0 END) AS vaccinated
                                            FROM employees e
                                            GROUP BY e.service";
                                        $result = $conn->query($sql);
                                        while ($row = $result->fetch_assoc()) {
                                            $percentage = ($row['total'] > 0) ? round(($row['vaccinated'] / $row['total']) * 100, 2) : 0;
                                            echo "<tr>
                                                <td>{$row['service']}</td>
                                                <td>{$percentage}%</td>
                                              </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Nombre de vaccinations par type de vaccin</h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Type de vaccin</th>
                                            <th>Nombre</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT type_vaccin, COUNT(*) AS count
                                            FROM vaccinations
                                            GROUP BY type_vaccin
                                            ORDER BY count DESC";
                                        $result = $conn->query($sql);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                <td>{$row['type_vaccin']}</td>
                                                <td>{$row['count']}</td>
                                              </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour la liste des employés -->
        <div class="modal fade" id="employeeListModal" tabindex="-1" aria-labelledby="employeeListModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="employeeListModalLabel">Liste des employés</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Sexe</th>
                                        <th>Date de naissance</th>
                                        <th>Grade</th>
                                        <th>Service</th>
                                        <th>N° Telephone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT id, nom, prenom, sexe, date_naissance, grade, service, telephone FROM employees";
                                    $result = $conn->query($sql);
                                    $i = 1; // Initialiser le compteur ici
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
											<td>{$i}</td> <!-- Affiche l'index -->
											<td>{$row['nom']}</td>
											<td>{$row['prenom']}</td>
											<td>{$row['sexe']}</td>
											<td>" . date('d/m/Y', strtotime($row['date_naissance'])) . "</td>
											<td>{$row['grade']}</td>
											<td>{$row['service']}</td>
											<td>{$row['telephone']}</td>
										</tr>";
                                        $i++; // Incrémentation du compteur après l'affichage
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour la liste des employés non vaccinés -->
        <div class="modal fade" id="nonVaccinatedModal" tabindex="-1" aria-labelledby="nonVaccinatedModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nonVaccinatedModalLabel">Liste des employés non vaccinés</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Sexe</th>
                                        <th>Date de naissance</th>
                                        <th>Grade</th>
                                        <th>Service</th>
                                        <th>N° Telephone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT id, nom, prenom, sexe, date_naissance, grade, service, telephone 
                                    FROM employees 
                                    WHERE id NOT IN (SELECT DISTINCT employee_id FROM vaccinations)";
                                    $result = $conn->query($sql);
                                    $i = 1; // Initialiser le compteur ici
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                        <td>{$i}</td> <!-- Affiche l'index -->
                                        <td>{$row['nom']}</td>
                                        <td>{$row['prenom']}</td>
                                        <td>{$row['sexe']}</td>
                                        <td>" . date('d/m/Y', strtotime($row['date_naissance'])) . "</td>
                                        <td>{$row['grade']}</td>
                                        <td>{$row['service']}</td>
										<td>{$row['telephone']}</td>
                                      </tr>";
                                        $i++; // Incrémentation du compteur après l'affichage
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Bootstrap 5 JS Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Fonction de recherche pour l'historique des vaccinations
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });


        // Fonction de recherche pour les prochains rappels
        document.getElementById('searchReminders').addEventListener('input', function () {
            const input = this.value.toLowerCase();
            const tableRows = document.querySelectorAll(' tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        });

    </script>
    <script>
        // Met à jour l'année dynamique dans l'élément avec l'ID 'currentYear'
        document.getElementById("currentYear").textContent = new Date().getFullYear();
    </script>
</body>

</html>