<?php
require_once __DIR__ . '/../config/db.php';

class Salle {
    private $id;
    private $nom;
    private $id_batiment;
    private $etage;
    private $capacite;
    private $equipements;
    private $description;
    private $disponible;
    private $statut_maintenance;
    private $date_creation;

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getIdBatiment() { return $this->id_batiment; }
    public function getEtage() { return $this->etage; }
    public function getCapacite() { return $this->capacite; }
    public function getEquipements() { return $this->equipements; }
    public function getDescription() { return $this->description; }
    public function getDisponible() { return $this->disponible; }
    public function getStatutMaintenance() { return $this->statut_maintenance; }
    public function getDateCreation() { return $this->date_creation; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setIdBatiment($id) { $this->id_batiment = $id; }
    public function setEtage($etage) { $this->etage = $etage; }
    public function setCapacite($cap) { $this->capacite = $cap; }
    public function setEquipements($eq) { $this->equipements = $eq; }
    public function setDescription($desc) { $this->description = $desc; }
    public function setDisponible($disp) { $this->disponible = $disp; }
    public function setStatutMaintenance($statut) { $this->statut_maintenance = $statut; }
    public function setDateCreation($date) { $this->date_creation = $date; }
}
