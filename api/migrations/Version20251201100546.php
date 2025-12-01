<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201100546 extends AbstractMigration
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
            ALTER TABLE player_titles ADD league_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_titles ADD CONSTRAINT fk_player_titles_leagues_league_id FOREIGN KEY (league_id) REFERENCES leagues (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_player_titles_league_id ON player_titles (league_id)
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
            ALTER TABLE player_titles DROP FOREIGN KEY fk_player_titles_leagues_league_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_player_titles_league_id ON player_titles
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_titles DROP league_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
