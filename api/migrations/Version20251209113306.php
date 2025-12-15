<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209113306 extends AbstractMigration
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
            ALTER TABLE task_assignment ADD calendar_event_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment 
            ADD CONSTRAINT fk_task_assignment_calendar_events_calendar_event_id FOREIGN KEY (calendar_event_id) REFERENCES calendar_events (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_task_assignment_calendar_event_id ON task_assignment (calendar_event_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task 
            ADD assigned_date DATE DEFAULT NULL,
            CHANGE is_recurring is_recurring TINYINT(1) DEFAULT 0 NOT NULL
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
            ALTER TABLE task DROP assigned_date, CHANGE is_recurring is_recurring TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment DROP FOREIGN KEY fk_task_assignment_calendar_events_calendar_event_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_task_assignment_calendar_event_id ON task_assignment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment DROP calendar_event_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
