<?php

namespace Entity;

/**
 * Questions
 * 
 * @Table(name = "questions")
 * @Entity
 */

class questions {
    
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
     * @column(name="question", type="string", nullable=false)
     */
    private $question;
    
    /**
     * @var string
     * 
     * @column(name="opta", type="string", nullable=false)
     */
    private $opta;
    
    /**
     * @var string
     * 
     * @column(name="optb", type="string", nullable=false)
     */
    private $optb;
    
    /**
     * @var string
     * 
     * @column(name="optc", type="string", nullable=false)
     */
    private $optc;
    
    /**
     * @var string
     * 
     * @column(name="optd", type="string", nullable=false)
     */
    private $optd;
    
    /**
     * @var string
     * 
     * @column(name="answer", type="string", nullable=false)
     */
    private $answer;
    
    /**
     * Get id
     * 
     * @return integer
     */
    public function getId() {
        return $this->id;
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
     * Set opta
     * 
     * @param string $opta
     * @return opta
     */
    public function setOptA($opta) {
        $this->question = $opta;
    }
    
    /**
     * Set optb
     * 
     * @param string $optb
     * @return optb
     */
    public function setOptB($optb) {
        $this->question = $optb;
    }
    
    /**
     * Set optc
     * 
     * @param string $optc
     * @return optc
     */
    public function setOptC($optc) {
        $this->question = $optc;
    }
    
    /**
     * Set optd
     * 
     * @param string $optd
     * @return optd
     */
    public function setOptD($optd) {
        $this->question = $optd;
    }
    
    /**
     * Set answer
     * 
     * @param string $answer
     * @return answer
     */
    public function setAnswer($answer) {
        $this->question = $answer;
    }
    
    /**
     * Get answer
     * 
     * @return string
     */
    public function getAnswer() {
        return $this->answer;
    }
}

