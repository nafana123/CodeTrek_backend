<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250127182953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite_task (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_BC78C21A8DB60186 (task_id), INDEX IDX_BC78C21AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE favorite_task ADD CONSTRAINT FK_BC78C21A8DB60186 FOREIGN KEY (task_id) REFERENCES task (task_id)');
        $this->addSql('ALTER TABLE favorite_task ADD CONSTRAINT FK_BC78C21AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite_task DROP FOREIGN KEY FK_BC78C21A8DB60186');
        $this->addSql('ALTER TABLE favorite_task DROP FOREIGN KEY FK_BC78C21AA76ED395');
        $this->addSql('DROP TABLE favorite_task');
    }
}
