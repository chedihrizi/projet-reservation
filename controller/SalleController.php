<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../Model/Salle.php';

class SalleController {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $query = $this->db->query("
            SELECT s.*, b.nom as nom_batiment
            FROM salle s
            JOIN batiment b ON s.id_batiment = b.id
            ORDER BY s.id DESC
        ");
        return $query->fetchAll();
    }

    public function getById($id) {
        $query = $this->db->prepare("
            SELECT s.*, b.nom as nom_batiment
            FROM salle s
            JOIN batiment b ON s.id_batiment = b.id
            WHERE s.id = ?
        ");
        $query->execute([$id]);
        return $query->fetch();
    }

    public function getAvailable($date, $heure_debut, $heure_fin) {
        $query = $this->db->prepare("
            SELECT s.*, b.nom as nom_batiment
            FROM salle s
            JOIN batiment b ON s.id_batiment = b.id
            WHERE s.disponible = 1
            AND s.statut_maintenance = 0
            AND s.id NOT IN (
                SELECT r.id_salle FROM reservation r
                WHERE r.date_reservation = ?
                AND r.statut IN ('en_attente', 'validee')
                AND r.heure_debut < ?
                AND r.heure_fin > ?
            )
            ORDER BY b.nom, s.nom
        ");
        $query->execute([$date, $heure_fin, $heure_debut]);
        return $query->fetchAll();
    }

    public function getAllDisponibles() {
        $query = $this->db->query("
            SELECT s.*, b.nom as nom_batiment
            FROM salle s
            JOIN batiment b ON s.id_batiment = b.id
            WHERE s.disponible = 1
            AND s.statut_maintenance = 0
            ORDER BY b.nom, s.nom
        ");
        return $query->fetchAll();
    }

    public function add($nom, $id_batiment, $etage, $capacite, $equipements, $description, $disponible, $statut_maintenance) {
        $query = $this->db->prepare("INSERT INTO salle (nom, id_batiment, etage, capacite, equipements, description, disponible, statut_maintenance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $query->execute([$nom, $id_batiment, $etage, $capacite, $equipements, $description, $disponible, $statut_maintenance]);
    }

    public function update($id, $nom, $id_batiment, $etage, $capacite, $equipements, $description, $disponible, $statut_maintenance) {
        $query = $this->db->prepare("UPDATE salle SET nom = ?, id_batiment = ?, etage = ?, capacite = ?, equipements = ?, description = ?, disponible = ?, statut_maintenance = ? WHERE id = ?");
        return $query->execute([$nom, $id_batiment, $etage, $capacite, $equipements, $description, $disponible, $statut_maintenance, $id]);
    }

    public function delete($id) {
        try {
            $queryRes = $this->db->prepare("DELETE FROM reservation WHERE id_salle = ?");
            $queryRes->execute([$id]);

            $query = $this->db->prepare("DELETE FROM salle WHERE id = ?");
            return $query->execute([$id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function exists($id) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM salle WHERE id = ?");
        $query->execute([$id]);
        return $query->fetchColumn() > 0;
    }

    public function countAll() {
        $query = $this->db->query("SELECT COUNT(*) FROM salle");
        return $query->fetchColumn();
    }

    public function countDisponibles() {
        $query = $this->db->query("SELECT COUNT(*) FROM salle WHERE disponible = 1 AND statut_maintenance = 0");
        return $query->fetchColumn();
    }

    public function getByBatiment($id_batiment) {
        $query = $this->db->prepare("
            SELECT s.*, b.nom as nom_batiment
            FROM salle s
            JOIN batiment b ON s.id_batiment = b.id
            WHERE s.id_batiment = ?
            ORDER BY s.etage, s.nom
        ");
        $query->execute([$id_batiment]);
        return $query->fetchAll();
    }

    public function toggleDisponibilite($id) {
        $query = $this->db->prepare("UPDATE salle SET disponible = NOT disponible WHERE id = ?");
        return $query->execute([$id]);
    }

    public function toggleMaintenance($id) {
        $query = $this->db->prepare("UPDATE salle SET statut_maintenance = NOT statut_maintenance WHERE id = ?");
        return $query->execute([$id]);
    }
}
