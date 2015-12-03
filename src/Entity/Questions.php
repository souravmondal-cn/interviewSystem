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
     * @column(name="qid", type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $qid;

    /**
     * @var string
     * 
     * @column(name="question", type="string")
     */
    private $question;

    /**
     * @var string
     * 
     * @column(name="optionA", type="string")
     */
    private $optionA;

    /**
     * @var string
     * 
     * @column(name="optionB", type="string")
     */
    private $optionB;

    /**
     * @var string
     * 
     * @column(name="optionC", type="string")
     */
    private $optionC;

    /**
     * @var string
     * 
     * @column(name="optionD", type="string")
     */
    private $optionD;

    /**
     * @var string
     * 
     * @column(name="answer", type="string")
     */
    private $answer;

    /**
     * @var \Entity\Category
     *
     * @ManyToOne(targetEntity="Entity\Category")
     * @JoinColumns({
     *   @JoinColumn(name="categoryId", referencedColumnName="cid", nullable=true)
     * })
     */
    private $categoryId;

    /**
     * Get qid
     * 
     * @return integer
     */
    public function getQId() {
        return $this->qid;
    }

    /**
     * Set question
     * 
     * @param string $question
     * @return question
     */
    public function setQuestion($question) {
        $this->question = $question;
    }

    /**
     * Get question
     * 
     * @return string
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * Set $optionA
     * 
     * @param string $optionA
     * @return optionA
     */
    public function setOptionA($optionA) {
        $this->optionA = $optionA;
    }

    /**
     * Get $optionA
     * 
     * @return string
     */
    public function getOptionA() {
        return $this->optionA;
    }

    /**
     * Set $optionB
     * 
     * @param string $optionB
     * @return optionB
     */
    public function setOptionB($optionB) {
        $this->optionB = $optionB;
    }

    /**
     * Get $optionC
     * 
     * @return string
     */
    public function getOptionB() {
        return $this->optionC;
    }

    /**
     * Set $optionC
     * 
     * @param string $optionC
     * @return optionC
     */
    public function setOptionC($optionC) {
        $this->optionC = $optionC;
    }

    /**
     * Get $optionC
     * 
     * @return string
     */
    public function getOptionC() {
        return $this->optionC;
    }

    /**
     * Set $optionD
     * 
     * @param string $optionD
     * @return optionD
     */
    public function setOptionD($optionD) {
        $this->optionD = $optionD;
    }

    /**
     * Get $optionD
     * 
     * @return string
     */
    public function getOptionD() {
        return $this->optionD;
    }

    /**
     * Set $answer
     * 
     * @param string $answer
     * @return answer
     */
    public function setAnswer($answer) {
        $this->answer = $answer;
    }

    /**
     * Get $answer
     * 
     * @return string
     */
    public function getAnswer() {
        return $this->answer;
    }

    /**
     * Set $categoryId
     * 
     * @param \Entity\Category $categoryId
     * @return categoryId
     */
    public function setCategoryId($categoryId) {
        $this->categoryId = $categoryId;
    }

    /**
     * Get $categoryId
     * 
     * @return \Entity\Category
     */
    public function getCategoryId() {
        return $this->categoryId;
    }

    public function __toString() {
        return $this->setCategoryId($categoryId);
    }

}
