<?php
require_once __DIR__ . '/../config/db.php';

class Utilisateur {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $telephone;
    private $role;
    private $date_creation;

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMotDePasse() { return $this->mot_de_passe; }
    public function getTelephone() { return $this->telephone; }
    public function getRole() { return $this->role; }
    public function getDateCreation() { return $this->date_creation; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePasse($mdp) { $this->mot_de_passe = $mdp; }
    public function setTelephone($tel) { $this->telephone = $tel; }
    public function setRole($role) { $this->role = $role; }
    public function setDateCreation($date) { $this->date_creation = $date; }
}
