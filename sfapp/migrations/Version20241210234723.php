<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241210234723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE down (id INT AUTO_INCREMENT NOT NULL, sa_id INT NOT NULL, reason VARCHAR(255) DEFAULT NULL, temperature TINYINT(1) NOT NULL, humidity TINYINT(1) NOT NULL, co2 TINYINT(1) NOT NULL, microcontroller TINYINT(1) NOT NULL, date DATETIME NOT NULL, INDEX IDX_1CFF903B62CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE norm (id INT AUTO_INCREMENT NOT NULL, humidity_min_norm INT NOT NULL, humidity_max_norm INT NOT NULL, temperature_min_norm INT NOT NULL, temperature_max_norm INT NOT NULL, co2_min_norm INT NOT NULL, co2_max_norm INT NOT NULL, season VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, roomName VARCHAR(255) NOT NULL, idSa INT DEFAULT NULL, nb_radiator INT NOT NULL, nb_windows INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sa (id INT AUTO_INCREMENT NOT NULL, room_id INT DEFAULT NULL, state VARCHAR(255) NOT NULL, temperature INT DEFAULT NULL, humidity INT DEFAULT NULL, co2 INT DEFAULT NULL, UNIQUE INDEX UNIQ_7F7E690454177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE down ADD CONSTRAINT FK_1CFF903B62CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('ALTER TABLE sa ADD CONSTRAINT FK_7F7E690454177093 FOREIGN KEY (room_id) REFERENCES room (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE down DROP FOREIGN KEY FK_1CFF903B62CAE146');
        $this->addSql('ALTER TABLE sa DROP FOREIGN KEY FK_7F7E690454177093');
        $this->addSql('DROP TABLE down');
        $this->addSql('DROP TABLE norm');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE sa');
        $this->addSql('DROP TABLE user');
    }
}
