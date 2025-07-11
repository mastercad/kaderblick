<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Makes related_user_id nullable in user_relations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_relations MODIFY related_user_id INT NULL');
    }

    public function down(Schema $schema): void
    {
        // Warnung: Dies könnte fehlschlagen, wenn NULL-Werte existieren
        $this->addSql('ALTER TABLE user_relations MODIFY related_user_id INT NOT NULL');
    }
}
