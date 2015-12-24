<?php

namespace Entity;

/**
 * User
 * 
 * @Table(name = "users")
 * @Entity
 */
class User {

    /**
     * @var integer
     * 
     * @Column(type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * 
     * @Column(type="string", length=30, nullable=false, unique = true)
     */
    private $userName;

    /**
     * @var string
     * 
     * @Column(type="string", length=100, nullable=false, unique = true)
     */
    private $userEmail;

    /**
     * @var string
     * 
     * @Column(type="string", nullable=false)
     */
    private $password;

    /**
     * @var string
     * 
     * @Column(type="string", nullable=true)
     */
    private $officeLocation;

    /**
     * @var string
     * 
     * @Column(type="string", nullable=true)
     */
    private $userAddress;

    /**
     * @var boolean
     * 
     * @Column(type="boolean", nullable=false)
     */
    private $isAdmin = 0;

    /**
     * @var boolean
     * 
     * @Column(type="boolean", nullable=false)
     */
    private $allowAccess = 0;
    
    public function getId() {
        return $this->id;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function getUserEmail() {
        return $this->userEmail;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getOfficeLocation() {
        return $this->officeLocation;
    }

    public function getUserAddress() {
        return $this->userAddress;
    }

    public function getIsAdmin() {
        return $this->isAdmin;
    }

    public function getAllowAccess() {
        return $this->allowAccess;
    }
    
    public function setUserName($userName) {
        $this->userName = $userName;
        return $this;
    }

    public function setUserEmail($userEmail) {
        $this->userEmail = $userEmail;
        return $this;
    }

    public function setPassword($password) {
        $this->password = md5($password);
        return $this;
    }

    public function setOfficeLocation($officeLocation) {
        $this->officeLocation = $officeLocation;
        return $this;
    }

    public function setUserAddress($userAddress) {
        $this->userAddress = $userAddress;
        return $this;
    }

    public function setIsAdmin($isAdmin) {
        $this->isAdmin = $isAdmin;
        return $this;
    }

    public function setAllowAccess($allowAccess) {
        $this->allowAccess = $allowAccess;
        return $this;
    }
}
