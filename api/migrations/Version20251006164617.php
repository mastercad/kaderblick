<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006164617 extends AbstractMigration
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
            CREATE TABLE xp_rules (
              id INT AUTO_INCREMENT NOT NULL,
              action_type VARCHAR(50) NOT NULL,
              label VARCHAR(100) NOT NULL,
              xp_value INT NOT NULL,
              daily_limit INT DEFAULT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_levels (
              user_id INT NOT NULL,
              xp_total INT NOT NULL,
              level INT NOT NULL,
              updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              PRIMARY KEY(user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_xp_events (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              action_type VARCHAR(50) NOT NULL,
              action_id INT DEFAULT NULL,
              xp_value INT NOT NULL,
              is_processed TINYINT(1) DEFAULT 0 NOT NULL,
              meta JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX idx_user_xp_events_user_id (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_levels
            ADD
              CONSTRAINT fk_user_levels_users_user_id FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_xp_events
            ADD
              CONSTRAINT fk_user_xp_events_users_user_id FOREIGN KEY (user_id) REFERENCES users (id)
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
            ALTER TABLE user_levels DROP FOREIGN KEY fk_user_levels_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_xp_events DROP FOREIGN KEY fk_user_xp_events_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE xp_rules
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_levels
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_xp_events
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
