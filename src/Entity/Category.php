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
     * @Column(type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * 
     * @Column(type="string")
     */
    private $categoryName;

    /**
     * @var integer
     * 
     * @Column(type="integer")
     */
    private $parentId;

    public function getId() {
        return $this->id;
    }

    public function getCategoryName() {
        return $this->categoryName;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function setCategoryName($categoryName) {
        $this->categoryName = $categoryName;
        return $this;
    }

    public function setParentId($parentId) {
        $this->parentId = $parentId;
        return $this;
    }

}
