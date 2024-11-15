<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115165509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sa ADD CONSTRAINT FK_7F7E690454177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F7E690454177093 ON sa (room_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sa DROP FOREIGN KEY FK_7F7E690454177093');
        $this->addSql('DROP INDEX UNIQ_7F7E690454177093 ON sa');
    }
}
