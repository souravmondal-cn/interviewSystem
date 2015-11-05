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
}

