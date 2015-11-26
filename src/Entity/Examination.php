<?php

namespace Entity;

/**
 * Examination
 * 
 * @Table(name = "examination")
 * @Entity
 */

class Examination {
    
    /**
     * @var integer
     * 
     * @Column(name="examid", type="integer", unique=true)
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $examid;
    
    /**
     * @var string
     * 
     * @Column(name="email", type="string", length=100)
     */
    private $email;
    
    /**
     *  @var string
     * 
     * @Column(name="questions", type="string", nullable=false)
     */
    private $questions;
    
    /**
     * @var integer
     * 
     * @Column(name="total_questions", type="integer")
     */
    private $total_questions;
    
    /**
     * @var integer
     * 
     * @Column(name="correct_answers", type="integer", nullable=true)
     */
    private $correct_answers;
    
    /**
     *  @var integer
     * 
     *  @Column(name="totaltime", type="integer", nullable=false)
     */
    private $totaltime;
    
    /**
     * @var datetime
     * 
     * @Column(name="date_created", type="datetime", nullable=true)
     */
    private $date_created;
    
    /**
     * @var datetime
     * 
     * @Column(name="date_completed", type="datetime", nullable=true) 
     */
    private $date_completed;
    
    /**
     *@var boolean
     * 
     * @Column(name="is_qualified", type="boolean") 
     */
    private $is_qualified = false;
    
    /**
     * Get examid
     * 
     * @return integer
     */
    public function getExamId() {
        return $this->examid;
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
     * Set questions
     * 
     * @param string $questions
     * @return questions
     */
    public function setQuestions($questions) {
        $this->questions = $questions;
    }
    
    /**
     * Get questions
     * 
     * @return string
     */
    public function getQuestions() {
        return $this->questions;
    }
    
    /**
     * Set totaltime
     * 
     * @param string $totaltime
     * @return integer
     */
    public function setTotalTime($totaltime) {
        $this->totaltime = $totaltime;
    }
    
    /**
     * Get totaltime
     * 
     * @return integer
     */
    public function getTotalTime() {
        return $this->totaltime;
    }
    
    /**
     * set date_created
     * 
     * @param datetime $date_created
     * @return date_created
     */
    public function setDate_Created($date_created) {
        
        $this->date_created = $date_created;
    }
    
    /**
     * get date_created
     * 
     * @return datetime
     */
    public function getDate_Created() {
        return $this->date_created;
    }
    
    /**
     * set date_completed
     * 
     * @param datetime $date_completed
     * @return date_completed
     */
    public function setDate_Completed($date_completed) {
        $this->date_completed = $date_completed;
    }
    
    /**
     * get date_completed
     * 
     * @return datetime
     */
    public function getDate_Completed() {
        return $this->date_completed;
    }
    
    /**
     * set is_qualified
     * 
     * @param boolean $is_qualified
     * @return is_qualified
     */
    public function setIs_Qualified($is_qualified) {
        $this->is_qualified = $is_qualified;
    }
    
    /**
     * get is_qualified
     * 
     * @return boolean
     */
    public function getIs_Qualified() {
        return $this->is_qualified;
    }
    
    /**
     * set total_questions
     * 
     * @param integer $total_questions
     * @return total_questions
     */
    public function setTotal_Questions($total_questions) {
        $this->total_questions = $total_questions;
    }
    
    /**
     * get total_questions
     * 
     * @return integer
     */
    public function getTotal_Questions() {
        return $this->total_questions;
    }
    
    /**
     * set correct_answers
     * 
     * @param integer $correct_answers
     * @return correct_answers
     */
    public function setCorrect_Answers($correct_answers) {
        $this->correct_answers = $correct_answers;
    }
    
    /**
     * get correct_answers
     * 
     * @return integer
     */
    public function getCorrect_Answers() {
        return $this->correct_answers;
    }
}

