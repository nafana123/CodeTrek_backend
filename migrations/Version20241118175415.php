<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241118175415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_language (task_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_941B9BF98DB60186 (task_id), INDEX IDX_941B9BF982F1BAF4 (language_id), PRIMARY KEY(task_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_language ADD CONSTRAINT FK_941B9BF98DB60186 FOREIGN KEY (task_id) REFERENCES task (task_id)');
        $this->addSql('ALTER TABLE task_language ADD CONSTRAINT FK_941B9BF982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2582F1BAF4');
        $this->addSql('DROP INDEX IDX_527EDB2582F1BAF4 ON task');
        $this->addSql('ALTER TABLE task DROP language_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_language DROP FOREIGN KEY FK_941B9BF98DB60186');
        $this->addSql('ALTER TABLE task_language DROP FOREIGN KEY FK_941B9BF982F1BAF4');
        $this->addSql('DROP TABLE task_language');
        $this->addSql('ALTER TABLE task ADD language_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2582F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_527EDB2582F1BAF4 ON task (language_id)');
    }
}
