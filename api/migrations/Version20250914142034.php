<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250914142034 extends AbstractMigration
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
            CREATE TABLE task_assignment (
              id INT AUTO_INCREMENT NOT NULL,
              task_id INT NOT NULL,
              user_id INT NOT NULL,
              substitute_user_id INT DEFAULT NULL,
              swapped_with_assignment_id INT DEFAULT NULL,
              assigned_date DATE NOT NULL,
              status VARCHAR(32) NOT NULL,
              INDEX idx_task_assignment_task_id (task_id),
              INDEX idx_task_assignment_user_id (user_id),
              INDEX idx_task_assignment_substitute_user_id (substitute_user_id),
              INDEX idx_task_assignment_swapped_with_assignment_id (swapped_with_assignment_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE task (
              id INT AUTO_INCREMENT NOT NULL,
              created_by_id INT NOT NULL,
              title VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              is_recurring TINYINT(1) NOT NULL,
              recurrence_mode VARCHAR(32) DEFAULT 'classic' NOT NULL,
              recurrence_rule VARCHAR(255) DEFAULT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              rotation_count INT DEFAULT NULL,
              INDEX idx_task_created_by_id (created_by_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE task_rotation_users (
              task_id INT NOT NULL,
              user_id INT NOT NULL,
              INDEX idx_task_rotation_users_task_id (task_id),
              INDEX idx_task_rotation_users_user_id (user_id),
              PRIMARY KEY(task_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignment
            ADD
              CONSTRAINT fk_task_assignment_task_task_id FOREIGN KEY (task_id) REFERENCES task (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignment
            ADD
              CONSTRAINT fk_task_assignment_users_user_id FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignment
            ADD
              CONSTRAINT fk_task_assignment_users_substitute_user_id FOREIGN KEY (substitute_user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignment
            ADD
              CONSTRAINT fk_task_assignment_task_assignment_swapped_with_assignment_id FOREIGN KEY (swapped_with_assignment_id) REFERENCES task_assignment (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task
            ADD
              CONSTRAINT fk_task_users_created_by_id FOREIGN KEY (created_by_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_rotation_users
            ADD
              CONSTRAINT fk_task_rotation_users_task_task_id FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_rotation_users
            ADD
              CONSTRAINT fk_task_rotation_users_users_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
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
            ALTER TABLE task_assignment DROP FOREIGN KEY fk_task_assignment_task_task_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment DROP FOREIGN KEY fk_task_assignment_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_assignment DROP FOREIGN KEY fk_task_assignment_users_substitute_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              task_assignment
            DROP
              FOREIGN KEY fk_task_assignment_task_assignment_swapped_with_assignment_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task DROP FOREIGN KEY fk_task_users_created_by_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_rotation_users DROP FOREIGN KEY fk_task_rotation_users_task_task_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task_rotation_users DROP FOREIGN KEY fk_task_rotation_users_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE task_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE task
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE task_rotation_users
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
