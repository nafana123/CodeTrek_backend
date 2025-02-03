<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203162403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reply_to_message (id INT AUTO_INCREMENT NOT NULL, discussion_id INT DEFAULT NULL, user_id INT DEFAULT NULL, reply_to LONGTEXT NOT NULL, data DATETIME DEFAULT NULL, INDEX IDX_32E801DC1ADED311 (discussion_id), INDEX IDX_32E801DCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reply_to_message ADD CONSTRAINT FK_32E801DC1ADED311 FOREIGN KEY (discussion_id) REFERENCES discussion (id)');
        $this->addSql('ALTER TABLE reply_to_message ADD CONSTRAINT FK_32E801DCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reply_to_message DROP FOREIGN KEY FK_32E801DC1ADED311');
        $this->addSql('ALTER TABLE reply_to_message DROP FOREIGN KEY FK_32E801DCA76ED395');
        $this->addSql('DROP TABLE reply_to_message');
    }
}
