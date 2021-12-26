<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211215152016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase ADD purchased_at DATETIME'); // on accepte null
        $this->addSql('UPDATE purchase SET purchased_at = NOW()'); // on donne une date à toutes les lignes qui n’en avaient pas
        $this->addSql('ALTER TABLE purchase MODIFY purchased_at DATETIME NOT NULL'); // puis on modifie en not null
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase DROP purchase_at');
    }
}
