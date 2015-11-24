<?php

namespace Entity;

/**
 * Questions
 * 
 * @Table(name = "category")
 * @Entity
 */

class Category {
    
    /**
     * @var integer
     * 
     * @column(name="cid", type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $cid;
    
    /**
     * @var string
     * 
     * @column(name="categoryName", type="string")
     */
    private $categoryName;
    
    /**
     * @var integer
     * 
     * @column(name="parentId", type="integer")
     */
    private $parentId;
    
    /**
     * Get cid
     * 
     * @return integer
     */
    public function getCId() {
        return $this->cid;
    }
    
    /**
     * Set categoryName
     * 
     * @param string $categoryName
     * @return categoryName
     */
    public function setCategoryName($categoryName) {
        $this->categoryName = $categoryName;
    }
    
    /**
     * Get categoryName
     * 
     * @return string
     */
    public function getCategoryName() {
        return $this->categoryName;
    }
    
    /**
     * Set parentId
     * 
     * @param string $parentId
     * @return parentId
     */
    public function setParentId($parentId) {
        $this->parentId = $parentId;
    }
    
    /**
     * Get parentId
     * 
     * @return integer
     */
    public function getParentId() {
        return $this->parentId;
    }
    
    public function __toString() {
        return $this->getCategoryName();
    }
}