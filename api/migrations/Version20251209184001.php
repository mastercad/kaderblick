<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209184001 extends AbstractMigration
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
            ALTER TABLE task_assignment DROP FOREIGN KEY fk_task_assignment_task_assignment_swapped_with_assignment_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_task_assignment_swapped_with_assignment_id ON task_assignment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment DROP swapped_with_assignment_id
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
            ALTER TABLE task_assignment ADD swapped_with_assignment_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment ADD CONSTRAINT fk_task_assignment_task_assignment_swapped_with_assignment_id FOREIGN KEY (swapped_with_assignment_id) REFERENCES task_assignment (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_task_assignment_swapped_with_assignment_id ON task_assignment (swapped_with_assignment_id)
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
