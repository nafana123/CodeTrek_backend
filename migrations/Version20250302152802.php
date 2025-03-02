<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302152802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE test_case (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, input JSON NOT NULL, expected_output VARCHAR(255) NOT NULL, execution_template VARCHAR(255) NOT NULL, INDEX IDX_7D71B3CB8DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE test_case ADD CONSTRAINT FK_7D71B3CB8DB60186 FOREIGN KEY (task_id) REFERENCES task (task_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_case DROP FOREIGN KEY FK_7D71B3CB8DB60186');
        $this->addSql('DROP TABLE test_case');
    }
}
