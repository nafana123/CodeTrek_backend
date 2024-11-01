<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241030132407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task (task_id INT AUTO_INCREMENT NOT NULL, language_id INT DEFAULT NULL, difficulty_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_527EDB2582F1BAF4 (language_id), INDEX IDX_527EDB25FCFA9DAE (difficulty_id), PRIMARY KEY(task_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2582F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25FCFA9DAE FOREIGN KEY (difficulty_id) REFERENCES difficulty_levels (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2582F1BAF4');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25FCFA9DAE');
        $this->addSql('DROP TABLE task');
    }
}
