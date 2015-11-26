<?php

namespace ;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151125140259 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (cid INT AUTO_INCREMENT NOT NULL, categoryName VARCHAR(255) NOT NULL, parentId INT NOT NULL, PRIMARY KEY(cid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE examination (examid INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, questions VARCHAR(255) NOT NULL, total_questions INT NOT NULL, correct_answers INT DEFAULT NULL, totaltime INT NOT NULL, date_created DATETIME DEFAULT NULL, date_completed DATETIME DEFAULT NULL, is_qualified TINYINT(1) NOT NULL, PRIMARY KEY(examid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE questions (qid INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, optionA VARCHAR(255) NOT NULL, optionB VARCHAR(255) NOT NULL, optionC VARCHAR(255) NOT NULL, optionD VARCHAR(255) NOT NULL, answer VARCHAR(255) NOT NULL, categoryId INT DEFAULT NULL, INDEX IDX_8ADC54D59C370B71 (categoryId), PRIMARY KEY(qid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, user_address VARCHAR(255) DEFAULT NULL, is_admin TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D59C370B71 FOREIGN KEY (categoryId) REFERENCES category (cid)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D59C370B71');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE examination');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE users');
    }
}
