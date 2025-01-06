<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250106085657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE measure (id INT AUTO_INCREMENT NOT NULL, sa_id INT NOT NULL, value DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, capture_date DATETIME NOT NULL, INDEX IDX_8007192562CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE measure ADD CONSTRAINT FK_8007192562CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE measure DROP FOREIGN KEY FK_8007192562CAE146');
        $this->addSql('DROP TABLE measure');
    }
}
