<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260124163710 extends AbstractMigration
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
              tournament_matches
            DROP
              FOREIGN KEY fk_tournament_matches_tournament_teams_home_tournament_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            DROP
              FOREIGN KEY fk_tournament_matches_tournament_teams_away_tournament_team_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_tournament_matches_home_tournament_team_id ON tournament_matches
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_tournament_matches_away_tournament_team_id ON tournament_matches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              home_team_id INT DEFAULT NULL,
            ADD
              away_team_id INT DEFAULT NULL,
            DROP
              home_tournament_team_id,
            DROP
              away_tournament_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_teams_home_team_id FOREIGN KEY (home_team_id) REFERENCES teams (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_teams_away_team_id FOREIGN KEY (away_team_id) REFERENCES teams (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_tournament_matches_home_team_id ON tournament_matches (home_team_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_tournament_matches_away_team_id ON tournament_matches (away_team_id)
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
            ALTER TABLE tournament_matches DROP FOREIGN KEY fk_tournament_matches_teams_home_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_matches DROP FOREIGN KEY fk_tournament_matches_teams_away_team_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_tournament_matches_home_team_id ON tournament_matches
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_tournament_matches_away_team_id ON tournament_matches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              home_tournament_team_id INT DEFAULT NULL,
            ADD
              away_tournament_team_id INT DEFAULT NULL,
            DROP
              home_team_id,
            DROP
              away_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_tournament_teams_home_tournament_team_id FOREIGN KEY (home_tournament_team_id) REFERENCES tournament_teams (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_tournament_teams_away_tournament_team_id FOREIGN KEY (away_tournament_team_id) REFERENCES tournament_teams (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_tournament_matches_home_tournament_team_id ON tournament_matches (home_tournament_team_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_tournament_matches_away_tournament_team_id ON tournament_matches (away_tournament_team_id)
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
