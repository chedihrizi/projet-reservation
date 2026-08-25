<?php
require_once __DIR__ . '/../config/db.php';

class Reservation {
    private $id;
    private $id_utilisateur;
    private $id_salle;
    private $date_reservation;
    private $heure_debut;
    private $heure_fin;
    private $objet;
    private $statut;
    private $motif_refus;
    private $date_demande;

    // Getters
    public function getId() { return $this->id; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getIdSalle() { return $this->id_salle; }
    public function getDateReservation() { return $this->date_reservation; }
    public function getHeureDebut() { return $this->heure_debut; }
    public function getHeureFin() { return $this->heure_fin; }
    public function getObjet() { return $this->objet; }
    public function getStatut() { return $this->statut; }
    public function getMotifRefus() { return $this->motif_refus; }
    public function getDateDemande() { return $this->date_demande; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setIdUtilisateur($id) { $this->id_utilisateur = $id; }
    public function setIdSalle($id) { $this->id_salle = $id; }
    public function setDateReservation($date) { $this->date_reservation = $date; }
    public function setHeureDebut($heure) { $this->heure_debut = $heure; }
    public function setHeureFin($heure) { $this->heure_fin = $heure; }
    public function setObjet($objet) { $this->objet = $objet; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setMotifRefus($motif) { $this->motif_refus = $motif; }
    public function setDateDemande($date) { $this->date_demande = $date; }
}
