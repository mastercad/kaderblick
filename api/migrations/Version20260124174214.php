<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124174214 extends AbstractMigration
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
            ALTER TABLE tournaments ADD calendar_event_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournaments
            ADD
              CONSTRAINT fk_tournaments_calendar_events_calendar_event_id FOREIGN KEY (calendar_event_id) REFERENCES calendar_events (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_tournaments_calendar_event_id ON tournaments (calendar_event_id)
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
            ALTER TABLE tournaments DROP FOREIGN KEY fk_tournaments_calendar_events_calendar_event_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_tournaments_calendar_event_id ON tournaments
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournaments DROP calendar_event_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
