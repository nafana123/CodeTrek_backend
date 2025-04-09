<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250330103512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_case CHANGE input input LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE task CHANGE test_case test_case LONGTEXT NOT NULL');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_case CHANGE input input VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE task CHANGE test_case test_case VARCHAR(255) NOT NULL');

    }
}
