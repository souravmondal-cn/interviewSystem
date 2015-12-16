<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151216150506 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, categoryName VARCHAR(255) NOT NULL, parentId INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE examination (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, questions LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', totalQuestions INT NOT NULL, usersInput LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', correctAnswersCount INT DEFAULT NULL, totalTime INT NOT NULL, created DATETIME DEFAULT NULL, completed DATETIME DEFAULT NULL, isQualified TINYINT(1) NOT NULL, INDEX IDX_CCDAABC5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE questions (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT NOT NULL, optionA LONGTEXT NOT NULL, optionB LONGTEXT NOT NULL, optionC LONGTEXT NOT NULL, optionD LONGTEXT NOT NULL, answer VARCHAR(8) NOT NULL, categoryId INT NOT NULL, INDEX IDX_8ADC54D59C370B71 (categoryId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, userName VARCHAR(30) NOT NULL, userEmail VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, officeLocation VARCHAR(255) DEFAULT NULL, userAddress VARCHAR(255) DEFAULT NULL, isAdmin TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9586CA949 (userName), UNIQUE INDEX UNIQ_1483A5E91F76FA2 (userEmail), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE examination ADD CONSTRAINT FK_CCDAABC5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D59C370B71 FOREIGN KEY (categoryId) REFERENCES category (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D59C370B71');
        $this->addSql('ALTER TABLE examination DROP FOREIGN KEY FK_CCDAABC5A76ED395');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE examination');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE users');
    }
}
