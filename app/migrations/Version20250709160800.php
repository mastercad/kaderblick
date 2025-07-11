<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709160800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates feedback table for user feedback system';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE feedback (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            type VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            url VARCHAR(255) NOT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            resolved TINYINT(1) NOT NULL DEFAULT 0,
            admin_note TEXT DEFAULT NULL,
            screenshot_path VARCHAR(255) DEFAULT NULL,
            INDEX IDX_feedback_user (user_id),
            INDEX IDX_feedback_type (type),
            INDEX IDX_feedback_resolved (resolved),
            INDEX IDX_feedback_created_at (created_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign Key erst nach der Tabellenerstellung hinzufügen
        $this->addSql('ALTER TABLE feedback 
            ADD CONSTRAINT FK_D2294458A76ED395 
            FOREIGN KEY (user_id) 
            REFERENCES users (id) 
            ON DELETE CASCADE;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE feedback');
    }
}
