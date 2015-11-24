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
     * @column(type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     * 
     * @column(name="username", type="string", length=100)
     */
    private $username;
    
    /**
     * @var string
     * 
     * @column(name="email", type="string", length=100, nullable=false, unique = true)
     */
    private $email;
    
    /**
     * @var string
     * 
     * @column(name="password", type="string", nullable=false)
     */
    private $password;
    
    /**
     * @var string
     * 
     * @column(name="location", type="string", nullable=true)
     */
    private $location;
    
    /**
     * @var string
     * 
     * @column(name="user_address", type="string", nullable=true)
     */
    private $user_address;
    
     /**
     * @var boolean
     * 
     * @column(name="is_admin", type="boolean")
     */
    private $is_admin;
    
    /**
     * Get id
     * 
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Set username
     * 
     * @param string $username
     * @return username
     */
    public function setUserName($username) {
        $this->username = $username;
    }
    
    /**
     * Get username
     * 
     * @return string
     */
    public function getUserName() {
        return $this->username;
    }
    
    /**
     * Set email
     * 
     * @param string $email
     * @return email
     */
    public function setEmail($email) {
        $this->email = $email;
    }
    
    /**
     * Get email
     * 
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }
    
    /**
     * Set password
     * 
     * @param string $password
     * @return password
     */
    public function setPassword($password) {
        $this->password = $password;
    }
    
    /**
     * Get password
     * 
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }
    
    /**
     * Set location
     * 
     * @param string $location
     * @return location
     */
    public function setLocation($location) {
        $this->location = $location;
    }
    
    /**
     * Get location
     * 
     * @return string
     */
    public function getLocation() {
        return $this->location;
    }
    
    /**
     * Set user_address
     * 
     * @param string $user_address
     * @return user_address
     */
    public function setUser_Address($user_address) {
        $this->user_address = $user_address;
    }
    
    /**
     * Get user_address
     * 
     * @return string
     */
    public function getUser_Address() {
        return $this->user_address;
    }
    /**
     * Set is_admin
     * 
     * @param boolean $is_admin
     * @return is_admin
     */
    public function setIs_Admin($is_admin) {
        $this->is_admin = $is_admin;
    }
    
    /**
     * Get is_admin
     * 
     * @return boolean
     */
    public function getIs_admin() {
        return $this->is_admin;
    }
}

