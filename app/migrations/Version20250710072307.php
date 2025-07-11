<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710072307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE push_subscriptions (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, endpoint VARCHAR(500) NOT NULL, public_key VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, INDEX IDX_3FEC449DA76ED395 (user_id), UNIQUE INDEX uniq_push_endpoint (endpoint), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE push_subscriptions ADD CONSTRAINT FK_3FEC449DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback CHANGE message message LONGTEXT NOT NULL, CHANGE resolved resolved TINYINT(1) NOT NULL, CHANGE admin_note admin_note LONGTEXT DEFAULT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback RENAME INDEX idx_feedback_user TO IDX_D2294458A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games CHANGE date date DATETIME NULL;
            UPDATE games SET date = NULL WHERE date = "" OR date IS NULL;
            UPDATE games SET date = CONCAT(date, " 00:00:00");
            ALTER TABLE games CHANGE date date DATETIME NOT NULL;
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_permission_identifier ON permissions
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_relation_type_identifier ON relation_types
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE push_subscriptions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_relation_type_identifier ON relation_types (identifier)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback CHANGE message message TEXT NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0 NOT NULL, CHANGE resolved resolved TINYINT(1) DEFAULT 0 NOT NULL, CHANGE admin_note admin_note TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games CHANGE date date DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_permission_identifier ON permissions (identifier)
        SQL);
    }
}
