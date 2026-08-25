<?php
require_once __DIR__ . '/../config/db.php';

class Batiment {
    private $id;
    private $nom;
    private $adresse;
    private $nombre_etages;
    private $description;
    private $date_creation;

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getAdresse() { return $this->adresse; }
    public function getNombreEtages() { return $this->nombre_etages; }
    public function getDescription() { return $this->description; }
    public function getDateCreation() { return $this->date_creation; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }
    public function setNombreEtages($etages) { $this->nombre_etages = $etages; }
    public function setDescription($desc) { $this->description = $desc; }
    public function setDateCreation($date) { $this->date_creation = $date; }
}
