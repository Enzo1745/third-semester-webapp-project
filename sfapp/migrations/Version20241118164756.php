<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241118164756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, id_sa INT DEFAULT NULL, num_salle VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_729F519BBF039003 (id_sa), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sa (id INT AUTO_INCREMENT NOT NULL, room_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, temperature INT DEFAULT NULL, humidity INT DEFAULT NULL, co2 INT DEFAULT NULL, UNIQUE INDEX UNIQ_7F7E690454177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519BBF039003 FOREIGN KEY (id_sa) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE sa ADD CONSTRAINT FK_7F7E690454177093 FOREIGN KEY (room_id) REFERENCES room (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519BBF039003');
        $this->addSql('ALTER TABLE sa DROP FOREIGN KEY FK_7F7E690454177093');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE sa');
        $this->addSql('DROP TABLE utilisateur');
    }
}
