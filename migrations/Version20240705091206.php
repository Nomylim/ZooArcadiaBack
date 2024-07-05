<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705091206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rapport_veterinaires (id INT AUTO_INCREMENT NOT NULL, animal_id INT NOT NULL, date DATE NOT NULL, nourriture VARCHAR(255) NOT NULL, grammage INT NOT NULL, etat_animal LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_7AAB42448E962C16 (animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rapport_veterinaires ADD CONSTRAINT FK_7AAB42448E962C16 FOREIGN KEY (animal_id) REFERENCES animaux (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rapport_veterinaires DROP FOREIGN KEY FK_7AAB42448E962C16');
        $this->addSql('DROP TABLE rapport_veterinaires');
    }
}
