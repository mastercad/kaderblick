<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928142523 extends AbstractMigration
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
            CREATE TABLE team_ride_passenger (
              id INT AUTO_INCREMENT NOT NULL,
              team_ride_id INT NOT NULL,
              user_id INT NOT NULL,
              INDEX idx_team_ride_passenger_team_ride_id (team_ride_id),
              INDEX idx_team_ride_passenger_user_id (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_ride (
              id INT AUTO_INCREMENT NOT NULL,
              event_id INT NOT NULL,
              driver_id INT NOT NULL,
              seats INT NOT NULL,
              note LONGTEXT DEFAULT NULL,
              INDEX idx_team_ride_event_id (event_id),
              INDEX idx_team_ride_driver_id (driver_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_ride_passenger
            ADD
              CONSTRAINT fk_team_ride_passenger_team_ride_team_ride_id FOREIGN KEY (team_ride_id) REFERENCES team_ride (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_ride_passenger
            ADD
              CONSTRAINT fk_team_ride_passenger_users_user_id FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_ride
            ADD
              CONSTRAINT fk_team_ride_calendar_events_event_id FOREIGN KEY (event_id) REFERENCES calendar_events (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_ride
            ADD
              CONSTRAINT fk_team_ride_users_driver_id FOREIGN KEY (driver_id) REFERENCES users (id)
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
            ALTER TABLE team_ride_passenger DROP FOREIGN KEY fk_team_ride_passenger_team_ride_team_ride_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_ride_passenger DROP FOREIGN KEY fk_team_ride_passenger_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_ride DROP FOREIGN KEY fk_team_ride_calendar_events_event_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_ride DROP FOREIGN KEY fk_team_ride_users_driver_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_ride_passenger
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_ride
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
