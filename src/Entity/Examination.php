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
     * @Column(type="integer")
     * @Id
     * @Generatedvalue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Entity\User
     *
     * @ManyToOne(targetEntity="Entity\User")
     * @JoinColumns({
     *   @JoinColumn(name="userId",referencedColumnName="id", nullable=false)
     * })
     */
    private $userId;

    /**
     * @var string
     * 
     * @Column(type="text", nullable=false)
     */
    private $questions;

    /**
     * @var integer
     * 
     * @Column(type="integer")
     */
    private $totalQuestions;

    /**
     * @var string
     * 
     * @Column(type="json_array", nullable=true)
     */
    private $usersInput;

    /**
     * @var integer
     * 
     * @Column(type="integer", nullable=true)
     */
    private $correctAnswersCount;

    /**
     *  @var integer
     * 
     *  @Column(type="integer", nullable=false)
     */
    private $totalTime;

    /**
     * @var datetime
     * 
     * @Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $created;

    /**
     * @var datetime
     * 
     * @Column(type="datetime", nullable=true) 
     */
    private $completed;

    /**
     * @var boolean
     * 
     * @Column(type="boolean") 
     */
    private $isQualified = false;

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getQuestions() {
        return $this->questions;
    }

    public function getTotalQuestions() {
        return $this->totalQuestions;
    }

    public function getUsersInput() {
        return $this->usersInput;
    }

    public function getCorrectAnswersCount() {
        return $this->correctAnswersCount;
    }

    public function getTotalTime() {
        return $this->totalTime;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getCompleted() {
        return $this->completed;
    }

    public function getIsQualified() {
        return $this->isQualified;
    }

    public function setUserId(\Entity\User $userId) {
        $this->userId = $userId;
        return $this;
    }

    public function setQuestions($questions) {
        $this->questions = $questions;
        return $this;
    }

    public function setTotalQuestions($totalQuestions) {
        $this->totalQuestions = $totalQuestions;
        return $this;
    }

    public function setUsersInput($usersInput) {
        $this->usersInput = $usersInput;
        return $this;
    }

    public function setCorrectAnswersCount($correctAnswersCount) {
        $this->correctAnswersCount = $correctAnswersCount;
        return $this;
    }

    public function setTotalTime($totalTime) {
        $this->totalTime = $totalTime;
        return $this;
    }

    public function setCreated(datetime $created) {
        $this->created = $created;
        return $this;
    }

    public function setCompleted($completed) {
        $this->completed = $completed;
        return $this;
    }

    public function setIsQualified($isQualified) {
        $this->isQualified = $isQualified;
        return $this;
    }

}
