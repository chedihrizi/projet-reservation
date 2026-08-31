<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../Model/Reservation.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../controller/UtilisateurController.php';
require_once __DIR__ . '/../controller/SalleController.php';

class ReservationController {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $query = $this->db->query("
            SELECT r.*, u.nom, u.prenom, u.email, s.nom as nom_salle, b.nom as nom_batiment
            FROM reservation r
            JOIN utilisateur u ON r.id_utilisateur = u.id
            JOIN salle s ON r.id_salle = s.id
            JOIN batiment b ON s.id_batiment = b.id
            ORDER BY r.date_reservation DESC, r.heure_debut DESC
        ");
        return $query->fetchAll();
    }

    public function getById($id) {
        $query = $this->db->prepare("
            SELECT r.*, u.nom, u.prenom, u.email, s.nom as nom_salle, b.nom as nom_batiment
            FROM reservation r
            JOIN utilisateur u ON r.id_utilisateur = u.id
            JOIN salle s ON r.id_salle = s.id
            JOIN batiment b ON s.id_batiment = b.id
            WHERE r.id = ?
        ");
        $query->execute([$id]);
        return $query->fetch();
    }

    public function getByUtilisateur($id_utilisateur) {
        $query = $this->db->prepare("
            SELECT r.*, s.nom as nom_salle, b.nom as nom_batiment
            FROM reservation r
            JOIN salle s ON r.id_salle = s.id
            JOIN batiment b ON s.id_batiment = b.id
            WHERE r.id_utilisateur = ?
            ORDER BY r.date_reservation DESC, r.heure_debut DESC
        ");
        $query->execute([$id_utilisateur]);
        return $query->fetchAll();
    }

    public function getByStatut($statut) {
        $query = $this->db->prepare("
            SELECT r.*, u.nom, u.prenom, u.email, s.nom as nom_salle, b.nom as nom_batiment
            FROM reservation r
            JOIN utilisateur u ON r.id_utilisateur = u.id
            JOIN salle s ON r.id_salle = s.id
            JOIN batiment b ON s.id_batiment = b.id
            WHERE r.statut = ?
            ORDER BY r.date_reservation DESC, r.heure_debut DESC
        ");
        $query->execute([$statut]);
        return $query->fetchAll();
    }

    public function checkConflict($id_salle, $date_reservation, $heure_debut, $heure_fin, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM reservation
                WHERE id_salle = ? AND date_reservation = ?
                AND statut IN ('en_attente', 'validee')
                AND heure_debut < ? AND heure_fin > ?";
        $params = [$id_salle, $date_reservation, $heure_fin, $heure_debut];

        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchColumn() > 0;
    }

    public function getConflictingReservations($id_salle, $date_reservation, $heure_debut, $heure_fin, $exclude_id = null) {
        $sql = "SELECT r.*, u.nom, u.prenom
                FROM reservation r
                JOIN utilisateur u ON r.id_utilisateur = u.id
                WHERE r.id_salle = ? AND r.date_reservation = ?
                AND r.statut IN ('en_attente', 'validee')
                AND r.heure_debut < ? AND r.heure_fin > ?";
        $params = [$id_salle, $date_reservation, $heure_fin, $heure_debut];

        if ($exclude_id) {
            $sql .= " AND r.id != ?";
            $params[] = $exclude_id;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }

    public function add($id_utilisateur, $id_salle, $date_reservation, $heure_debut, $heure_fin, $objet) {

        // Check for conflicts
        $conflicts = $this->getConflictingReservations($id_salle, $date_reservation, $heure_debut, $heure_fin);
        if (!empty($conflicts)) {
            $errors[] = "Conflit détecté : la salle est déjà réservée pour ce créneau.";
            foreach ($conflicts as $c) {
                $errors[] = "  → Réservation de " . $c['prenom'] . " " . $c['nom'] . " de " . substr($c['heure_debut'],0,5) . " à " . substr($c['heure_fin'],0,5);
            }
            return $errors;
        }

        $query = $this->db->prepare("INSERT INTO reservation (id_utilisateur, id_salle, date_reservation, heure_debut, heure_fin, objet, statut) VALUES (?, ?, ?, ?, ?, ?, 'en_attente')");
        $result = $query->execute([$id_utilisateur, $id_salle, $date_reservation, $heure_debut, $heure_fin, $objet]);
        
        // Envoyer l'email de confirmation automatiquement
        if ($result) {
            $res_id = $this->db->lastInsertId();
            $reservation = $this->getById($res_id);
            
            if ($reservation) {
                $htmlBody = reservationConfirmedTemplate($reservation);
                sendEmail(
                    $reservation['email'],
                    "Confirmation de votre réservation - " . $reservation['nom_salle'],
                    $htmlBody
                );
            }
        }
        
        return $result;
    }

    public function update($id, $id_salle, $date_reservation, $heure_debut, $heure_fin, $objet, $statut) {
        if ($this->checkConflict($id_salle, $date_reservation, $heure_debut, $heure_fin, $id)) {
            return false;
        }

        $query = $this->db->prepare("UPDATE reservation SET id_salle = ?, date_reservation = ?, heure_debut = ?, heure_fin = ?, objet = ?, statut = ? WHERE id = ?");
        return $query->execute([$id_salle, $date_reservation, $heure_debut, $heure_fin, $objet, $statut, $id]);
    }

    public function updateStatus($id, $statut, $motif_refus = null) {
        $query = $this->db->prepare("UPDATE reservation SET statut = ?, motif_refus = ? WHERE id = ?");
        $result = $query->execute([$statut, $motif_refus, $id]);

        if ($result && $statut === 'validee') {
            $res = $this->getById($id);
            if ($res) {
                sendEmail(
                    $res['email'],
                    "Réservation confirmée — SallesPro",
                    reservationConfirmedTemplate($res)
                );
            }
        }

        return $result;
    }

    public function delete($id) {
        $query = $this->db->prepare("DELETE FROM reservation WHERE id = ?");
        return $query->execute([$id]);
    }

    public function cancelByUser($id, $id_utilisateur) {
        $query = $this->db->prepare("UPDATE reservation SET statut = 'annulee' WHERE id = ? AND id_utilisateur = ? AND statut IN ('en_attente', 'validee')");
        return $query->execute([$id, $id_utilisateur]);
    }

    public function exists($id) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM reservation WHERE id = ?");
        $query->execute([$id]);
        return $query->fetchColumn() > 0;
    }

    public function countAll() {
        $query = $this->db->query("SELECT COUNT(*) FROM reservation");
        return $query->fetchColumn();
    }

    public function countByStatut($statut) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM reservation WHERE statut = ?");
        $query->execute([$statut]);
        return $query->fetchColumn();
    }

    public function getReservationsByDateRange($date_debut, $date_fin) {
        $query = $this->db->prepare("
            SELECT r.*, u.nom, u.prenom, s.nom as nom_salle, b.nom as nom_batiment
            FROM reservation r
            JOIN utilisateur u ON r.id_utilisateur = u.id
            JOIN salle s ON r.id_salle = s.id
            JOIN batiment b ON s.id_batiment = b.id
            WHERE r.date_reservation BETWEEN ? AND ?
            ORDER BY r.date_reservation, r.heure_debut
        ");
        $query->execute([$date_debut, $date_fin]);
        return $query->fetchAll();
    }

    public function getCalendarData($year, $month) {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));
        $query = $this->db->prepare("
            SELECT r.*, s.nom as nom_salle, b.nom as nom_batiment, u.nom, u.prenom
            FROM reservation r
            JOIN salle s ON r.id_salle = s.id
            JOIN batiment b ON s.id_batiment = b.id
            JOIN utilisateur u ON r.id_utilisateur = u.id
            WHERE r.date_reservation BETWEEN ? AND ?
            AND r.statut IN ('en_attente', 'validee')
            ORDER BY r.date_reservation, r.heure_debut
        ");
        $query->execute([$start, $end]);
        return $query->fetchAll();
    }

    public function search($search, $statut = null) {
        $sql = "SELECT r.*, u.nom, u.prenom, u.email, s.nom as nom_salle, b.nom as nom_batiment
                FROM reservation r
                JOIN utilisateur u ON r.id_utilisateur = u.id
                JOIN salle s ON r.id_salle = s.id
                JOIN batiment b ON s.id_batiment = b.id
                WHERE (r.objet LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ? OR s.nom LIKE ? OR b.nom LIKE ?)";

        $param = "%$search%";
        $params = [$param, $param, $param, $param, $param];

        if ($statut) {
            $sql .= " AND r.statut = ?";
            $params[] = $statut;
        }

        $sql .= " ORDER BY r.date_reservation DESC, r.heure_debut DESC";

        $query = $this->db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }
}
