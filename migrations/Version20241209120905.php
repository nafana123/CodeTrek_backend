<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209120905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE solved_task (id INT AUTO_INCREMENT NOT NULL, task_id INT DEFAULT NULL, user_id INT DEFAULT NULL, code LONGTEXT NOT NULL, INDEX IDX_7F5142698DB60186 (task_id), INDEX IDX_7F514269A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE solved_task ADD CONSTRAINT FK_7F5142698DB60186 FOREIGN KEY (task_id) REFERENCES task (task_id)');
        $this->addSql('ALTER TABLE solved_task ADD CONSTRAINT FK_7F514269A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solved_task DROP FOREIGN KEY FK_7F5142698DB60186');
        $this->addSql('ALTER TABLE solved_task DROP FOREIGN KEY FK_7F514269A76ED395');
        $this->addSql('DROP TABLE solved_task');
    }
}
