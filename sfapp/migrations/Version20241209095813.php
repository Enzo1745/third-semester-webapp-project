<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209095813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE down (id INT AUTO_INCREMENT NOT NULL, sa_id INT NOT NULL, reason VARCHAR(255) DEFAULT NULL, temperature TINYINT(1) NOT NULL, humidity TINYINT(1) NOT NULL, co2 TINYINT(1) NOT NULL, microcontroller TINYINT(1) NOT NULL, date DATETIME NOT NULL, INDEX IDX_1CFF903B62CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE down ADD CONSTRAINT FK_1CFF903B62CAE146 FOREIGN KEY (sa_id) REFERENCES sa (id)');
        $this->addSql('DROP TABLE norm');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE norm (id INT AUTO_INCREMENT NOT NULL, humidity_min_norm INT NOT NULL, humidity_max_norm INT NOT NULL, temperature_min_norm INT NOT NULL, temperature_max_norm INT NOT NULL, co2_min_norm INT NOT NULL, co2_max_norm INT NOT NULL, season VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE down DROP FOREIGN KEY FK_1CFF903B62CAE146');
        $this->addSql('DROP TABLE down');
    }
}
