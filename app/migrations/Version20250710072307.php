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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE push_subscriptions (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, endpoint VARCHAR(500) NOT NULL, public_key VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, INDEX IDX_3FEC449DA76ED395 (user_id), UNIQUE INDEX uniq_push_endpoint (endpoint), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE push_subscriptions ADD CONSTRAINT FK_3FEC449DA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widget DROP FOREIGN KEY FK_dashboard_widget_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widget ADD CONSTRAINT FK_6AC217EBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widget RENAME INDEX idx_dashboard_widget_user TO IDX_6AC217EBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_feedback_type ON feedback
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_feedback_resolved ON feedback
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_feedback_created_at ON feedback
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback CHANGE message message LONGTEXT NOT NULL, CHANGE resolved resolved TINYINT(1) NOT NULL, CHANGE admin_note admin_note LONGTEXT DEFAULT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
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
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_user_relations_player
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_user_relations_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_user_relations_related_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_user_relations_coach
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_148C329CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_148C329C98771930 FOREIGN KEY (related_user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_148C329C99E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_148C329C3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_user TO IDX_148C329CA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_related_user TO IDX_148C329C98771930
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_player TO IDX_148C329C99E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_coach TO IDX_148C329C3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX fk_user_relations_type TO IDX_148C329CDC379EE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME INDEX fk_1483a5e999e6f5df TO IDX_1483A5E999E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME INDEX fk_1483a5e93c105691 TO IDX_1483A5E93C105691
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE push_subscriptions DROP FOREIGN KEY FK_3FEC449DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE push_subscriptions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widget DROP FOREIGN KEY FK_6AC217EBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widget ADD CONSTRAINT FK_dashboard_widget_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widget RENAME INDEX idx_6ac217eba76ed395 TO IDX_dashboard_widget_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME INDEX idx_1483a5e999e6f5df TO FK_1483A5E999E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME INDEX idx_1483a5e93c105691 TO FK_1483A5E93C105691
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_relation_type_identifier ON relation_types (identifier)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_148C329CA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_148C329C98771930
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_148C329C99E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY FK_148C329C3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_user_relations_player FOREIGN KEY (player_id) REFERENCES players (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_user_relations_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_user_relations_related_user FOREIGN KEY (related_user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations ADD CONSTRAINT FK_user_relations_coach FOREIGN KEY (coach_id) REFERENCES coach (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_148c329c99e6f5df TO IDX_player
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_148c329c3c105691 TO IDX_coach
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_148c329ca76ed395 TO IDX_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_148c329cdc379ee2 TO FK_user_relations_type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations RENAME INDEX idx_148c329c98771930 TO IDX_related_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback CHANGE message message TEXT NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0 NOT NULL, CHANGE resolved resolved TINYINT(1) DEFAULT 0 NOT NULL, CHANGE admin_note admin_note TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_feedback_type ON feedback (type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_feedback_resolved ON feedback (resolved)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_feedback_created_at ON feedback (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback RENAME INDEX idx_d2294458a76ed395 TO IDX_feedback_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games CHANGE date date DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_permission_identifier ON permissions (identifier)
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
