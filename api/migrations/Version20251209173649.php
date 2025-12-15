<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209173649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql(<<<'SQL'
            CREATE TABLE calendar_event_permissions (id INT AUTO_INCREMENT NOT NULL, calendar_event_id INT NOT NULL, user_id INT DEFAULT NULL, team_id INT DEFAULT NULL, club_id INT DEFAULT NULL, permission_type VARCHAR(32) NOT NULL, INDEX idx_calendar_event_permissions_calendar_event_id (calendar_event_id), INDEX idx_calendar_event_permissions_user_id (user_id), INDEX idx_calendar_event_permissions_team_id (team_id), INDEX idx_calendar_event_permissions_club_id (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions ADD CONSTRAINT fk_calendar_event_permissions_calendar_events_calendar_event_id FOREIGN KEY (calendar_event_id) REFERENCES calendar_events (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions ADD CONSTRAINT fk_calendar_event_permissions_users_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions ADD CONSTRAINT fk_calendar_event_permissions_teams_team_id FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions ADD CONSTRAINT fk_calendar_event_permissions_clubs_club_id FOREIGN KEY (club_id) REFERENCES clubs (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD created_by_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT fk_calendar_events_users_created_by_id FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_calendar_events_created_by_id ON calendar_events (created_by_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions DROP FOREIGN KEY fk_calendar_event_permissions_calendar_events_calendar_event_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions DROP FOREIGN KEY fk_calendar_event_permissions_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions DROP FOREIGN KEY fk_calendar_event_permissions_teams_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_event_permissions DROP FOREIGN KEY fk_calendar_event_permissions_clubs_club_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar_event_permissions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY fk_calendar_events_users_created_by_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_calendar_events_created_by_id ON calendar_events
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP created_by_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
