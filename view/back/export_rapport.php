<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?action=login');
    exit();
}
require_once __DIR__ . '/../../controller/ReservationController.php';

$controller = new ReservationController();
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');

$results = $controller->getReservationsByDateRange($date_debut, $date_fin);

$countTotal = count($results);
$countValidees = 0;
$countRefusees = 0;
$countEnAttente = 0;
$countAnnulees = 0;
foreach ($results as $r) {
    if ($r['statut'] === 'validee') $countValidees++;
    elseif ($r['statut'] === 'refusee') $countRefusees++;
    elseif ($r['statut'] === 'en_attente') $countEnAttente++;
    elseif ($r['statut'] === 'annulee') $countAnnulees++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de réservation - <?php echo date('d/m/Y', strtotime($date_debut)); ?> au <?php echo date('d/m/Y', strtotime($date_fin)); ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; color: #333; }
        h1 { color: #602299; font-size: 22px; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 20px; }
        .summary { display: flex; gap: 15px; margin-bottom: 25px; }
        .summary-box { background: #f5f5f5; padding: 12px 20px; border-radius: 6px; text-align: center; min-width: 100px; }
        .summary-box .num { font-size: 24px; font-weight: 700; color: #602299; }
        .summary-box .lbl { font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th { background-color: #602299; color: white; padding: 10px 12px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 500; }
        .badge-validee { background: #D4EDDA; color: #155724; }
        .badge-refusee { background: #F8D7DA; color: #721C24; }
        .badge-en_attente { background: #FFF3CD; color: #856404; }
        .badge-annulee { background: #e2e3e5; color: #383d41; }
        .footer { margin-top: 30px; font-size: 11px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .print-btn { background: #602299; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; margin-bottom: 20px; }
        .print-btn:hover { background: #4a1a7a; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()"><i class="fas fa-print"></i> Imprimer / Enregistrer en PDF</button>

    <h1>SallesPro — Rapport de Réservation</h1>
    <p class="subtitle">Période : <?php echo date('d/m/Y', strtotime($date_debut)); ?> — <?php echo date('d/m/Y', strtotime($date_fin)); ?></p>

    <div class="summary">
        <div class="summary-box"><div class="num"><?php echo $countTotal; ?></div><div class="lbl">Total</div></div>
        <div class="summary-box"><div class="num"><?php echo $countValidees; ?></div><div class="lbl">Validées</div></div>
        <div class="summary-box"><div class="num"><?php echo $countEnAttente; ?></div><div class="lbl">En attente</div></div>
        <div class="summary-box"><div class="num"><?php echo $countRefusees; ?></div><div class="lbl">Refusées</div></div>
        <div class="summary-box"><div class="num"><?php echo $countAnnulees; ?></div><div class="lbl">Annulées</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Salle</th>
                <th>Bâtiment</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Objet</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['prenom'] . ' ' . $r['nom']); ?></td>
                <td><?php echo htmlspecialchars($r['nom_salle']); ?></td>
                <td><?php echo htmlspecialchars($r['nom_batiment']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($r['date_reservation'])); ?></td>
                <td><?php echo substr($r['heure_debut'],0,5) . ' - ' . substr($r['heure_fin'],0,5); ?></td>
                <td><?php echo htmlspecialchars($r['objet']); ?></td>
                <td><span class="badge badge-<?php echo $r['statut']; ?>"><?php echo ucfirst(str_replace('_', ' ', $r['statut'])); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Généré le <?php echo date('d/m/Y à H:i'); ?> — SallesPro
    </div>

    <script>
    window.onload = function() {
        setTimeout(function() { window.print(); }, 300);
    };
    </script>
</body>
</html>
