<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124182915 extends AbstractMigration
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
            CREATE TABLE user_titles (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, team_id INT DEFAULT NULL, title_category VARCHAR(50) NOT NULL, title_scope VARCHAR(20) NOT NULL, title_rank VARCHAR(20) NOT NULL, value INT NOT NULL, is_active TINYINT(1) NOT NULL, awarded_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', revoked_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', season VARCHAR(50) DEFAULT NULL, INDEX idx_user_titles_user_id (user_id), INDEX idx_user_titles_team_id (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_titles ADD CONSTRAINT fk_user_titles_users_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_titles ADD CONSTRAINT fk_user_titles_teams_team_id FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
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
            ALTER TABLE user_titles DROP FOREIGN KEY fk_user_titles_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_titles DROP FOREIGN KEY fk_user_titles_teams_team_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_titles
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
