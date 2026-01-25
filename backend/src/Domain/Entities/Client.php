<?php

class Client {
    private $id;
    private $email;
    private $name;
    private $surname;
    private $state;

    public function __construct($id = null,  $name = "", $surname = "", $email = "", $state = 1) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
        $this->state = $state;
    }

    // --- GETTERS ---

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getState() {
        return $this->state;
    }

    // --- SETTERS ---

    public function setId($id) {
        $this->id = $id;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setSurname($surname) {
        $this->surname = $surname;
    }

    public function setState($state) {
        $this->state = $state;
    }

    // Helper per comodità
    public function getFullName() {
        return trim($this->name . " " . $this->surname);
    }
}