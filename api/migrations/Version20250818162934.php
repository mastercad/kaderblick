<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250818162934 extends AbstractMigration
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
            CREATE TABLE report_definitions (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT DEFAULT NULL,
              name VARCHAR(100) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              config JSON NOT NULL COMMENT '(DC2Type:json)',
              is_template TINYINT(1) DEFAULT 0 NOT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX idx_report_definitions_user_id (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              report_definitions
            ADD
              CONSTRAINT fk_report_definitions_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widgets ADD report_definition_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              dashboard_widgets
            ADD
              CONSTRAINT fk_dashboard_widgets_report_definitions FOREIGN KEY (report_definition_id) REFERENCES report_definitions (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_dashboard_widgets_report_definition_id ON dashboard_widgets (report_definition_id)
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
            ALTER TABLE dashboard_widgets DROP FOREIGN KEY fk_dashboard_widgets_report_definitions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE report_definitions DROP FOREIGN KEY fk_report_definitions_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE report_definitions
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_dashboard_widgets_report_definition_id ON dashboard_widgets
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widgets DROP report_definition_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
