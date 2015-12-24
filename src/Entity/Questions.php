<?php

namespace Entity;

/**
 * Questions
 * 
 * @Table(name = "questions")
 * @Entity
 */
class Questions {

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
     * @Column(type="text")
     */
    private $question;

    /**
     * @var string
     * 
     * @Column(type="text")
     */
    private $optionA;

    /**
     * @var string
     * 
     * @Column(type="text")
     */
    private $optionB;

    /**
     * @var string
     * 
     * @Column(type="text")
     */
    private $optionC;

    /**
     * @var string
     * 
     * @Column(type="text")
     */
    private $optionD;

    /**
     * @var string
     * 
     * @Column(type="string", length=8)
     */
    private $answer;

    /**
     * @var \Entity\Category
     *
     * @ManyToOne(targetEntity="Entity\Category")
     * @JoinColumns({
     *   @JoinColumn(name="categoryId", referencedColumnName="id", nullable=false)
     * })
     */
    private $categoryId;
    
    /**
     * @var integer
     * 
     * @Column(type="integer")
     */
    private $difficultyLevel;

    public function getId() {
        return $this->id;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function getOptionA() {
        return $this->optionA;
    }

    public function getOptionB() {
        return $this->optionB;
    }

    public function getOptionC() {
        return $this->optionC;
    }

    public function getOptionD() {
        return $this->optionD;
    }

    public function getAnswer() {
        return $this->answer;
    }

    public function getCategoryId() {
        return $this->categoryId;
    }
    
    public function getDifficultyLevel() {
        return $this->difficultyLevel;
    }

    public function setQuestion($question) {
        $this->question = $question;
        return $this;
    }

    public function setOptionA($optionA) {
        $this->optionA = $optionA;
        return $this;
    }

    public function setOptionB($optionB) {
        $this->optionB = $optionB;
        return $this;
    }

    public function setOptionC($optionC) {
        $this->optionC = $optionC;
        return $this;
    }

    public function setOptionD($optionD) {
        $this->optionD = $optionD;
        return $this;
    }

    public function setAnswer($answer) {
        $this->answer = $answer;
        return $this;
    }

    public function setCategoryId(\Entity\Category $categoryId) {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function setDifficultyLevel($difficultyLevel) {
        $this->difficultyLevel = $difficultyLevel;
        return $this;
    }
}
