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
     * @column(name="examid", type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $examid;
    
    /**
     * @var string
     * 
     * @column(name="email", type="string", length=100, nullable=false)
     */
    private $email;
    
    /**
     *  @var string
     * 
     * @column(name="questions", type="string", nullable=false)
     */
    private $questions;
    
    /**
     * @var integer
     * 
     * @column(name="total_questions", type="integer")
     */
    private $total_questions;
    
    /**
     * @var integer
     * 
     * @column(name="correct_answers", type="integer", nullable=true)
     */
    private $correct_answers;
    
    /**
     *  @var integer
     * 
     *  @column(name="totaltime", type="integer", nullable=false)
     */
    private $totaltime;
    
    /**
     * @var datetime
     * 
     * @column(name="date_created", type="datetime", nullable=true)
     */
    private $date_created;
    
    /**
     * @var datetime
     * 
     * @column(name="date_completed", type="datetime", nullable=true) 
     */
    private $date_completed;
    
    /**
     *@var boolean
     * 
     * @column(name="is_qualified", type="boolean") 
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

