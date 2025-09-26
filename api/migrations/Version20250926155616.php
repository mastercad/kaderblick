<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926155616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql(<<<'SQL'
            CREATE TABLE weather_data (
              id INT NOT NULL,
              daily_weather_data JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              hourly_weather_data JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              weather_data
            ADD
              CONSTRAINT fk_weather_data_calendar_events_id FOREIGN KEY (id) REFERENCES calendar_events (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1010Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1010Platform'."
        );

        $this->addSql(<<<'SQL'
            ALTER TABLE weather_data DROP FOREIGN KEY fk_weather_data_calendar_events_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE weather_data
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
