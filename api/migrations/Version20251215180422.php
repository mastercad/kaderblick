<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215180422 extends AbstractMigration
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
            ALTER TABLE
              task_assignments RENAME INDEX idx_task_assignment_task_id TO idx_task_assignments_task_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX idx_task_assignment_user_id TO idx_task_assignments_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX idx_task_assignment_substitute_user_id TO idx_task_assignments_substitute_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX uniq_task_assignment_calendar_event_id TO uniq_task_assignments_calendar_event_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD offset_days INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks RENAME INDEX idx_task_created_by_id TO idx_tasks_created_by_id
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
            ALTER TABLE tasks DROP offset_days
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks RENAME INDEX idx_tasks_created_by_id TO idx_task_created_by_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX idx_task_assignments_substitute_user_id TO idx_task_assignment_substitute_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX uniq_task_assignments_calendar_event_id TO uniq_task_assignment_calendar_event_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX idx_task_assignments_task_id TO idx_task_assignment_task_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignments RENAME INDEX idx_task_assignments_user_id TO idx_task_assignment_user_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
