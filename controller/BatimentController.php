<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../Model/Batiment.php';

class BatimentController {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $query = $this->db->query("SELECT * FROM batiment ORDER BY id DESC");
        return $query->fetchAll();
    }

    public function getById($id) {
        $query = $this->db->prepare("SELECT * FROM batiment WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch();
    }

    public function add($nom, $adresse, $nombre_etages, $description) {
        $query = $this->db->prepare("INSERT INTO batiment (nom, adresse, nombre_etages, description) VALUES (?, ?, ?, ?)");
        return $query->execute([$nom, $adresse, $nombre_etages, $description]);
    }

    public function update($id, $nom, $adresse, $nombre_etages, $description) {
        $query = $this->db->prepare("UPDATE batiment SET nom = ?, adresse = ?, nombre_etages = ?, description = ? WHERE id = ?");
        return $query->execute([$nom, $adresse, $nombre_etages, $description, $id]);
    }

    public function delete($id) {
        try {
            $querySalles = $this->db->prepare("DELETE FROM salle WHERE id_batiment = ?");
            $querySalles->execute([$id]);

            $query = $this->db->prepare("DELETE FROM batiment WHERE id = ?");
            return $query->execute([$id]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function exists($id) {
        $query = $this->db->prepare("SELECT COUNT(*) FROM batiment WHERE id = ?");
        $query->execute([$id]);
        return $query->fetchColumn() > 0;
    }

    public function countAll() {
        $query = $this->db->query("SELECT COUNT(*) FROM batiment");
        return $query->fetchColumn();
    }
}
