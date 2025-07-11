<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds isRead column to feedback table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback ADD is_read TINYINT(1) NOT NULL DEFAULT 0');

        // Setze existierende gelöste Feedbacks auch als gelesen
        $this->addSql('UPDATE feedback SET is_read = 1 WHERE resolved = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE feedback DROP is_read');
    }
}
