<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104153614 extends AbstractMigration
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
            CREATE TABLE tournaments (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              type VARCHAR(50) NOT NULL,
              start_at DATETIME DEFAULT NULL,
              end_at DATETIME DEFAULT NULL,
              settings JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournament_matches (
              id INT AUTO_INCREMENT NOT NULL,
              tournament_id INT NOT NULL,
              home_tournament_team_id INT DEFAULT NULL,
              away_tournament_team_id INT DEFAULT NULL,
              game_id INT DEFAULT NULL,
              next_match_id INT DEFAULT NULL,
              round INT DEFAULT NULL,
              slot INT DEFAULT NULL,
              status VARCHAR(50) NOT NULL,
              scheduled_at DATETIME DEFAULT NULL,
              INDEX idx_tournament_matches_tournament_id (tournament_id),
              INDEX idx_tournament_matches_home_tournament_team_id (home_tournament_team_id),
              INDEX idx_tournament_matches_away_tournament_team_id (away_tournament_team_id),
              UNIQUE INDEX uniq_tournament_matches_game_id (game_id),
              INDEX idx_tournament_matches_next_match_id (next_match_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tournament_teams (
              id INT AUTO_INCREMENT NOT NULL,
              tournament_id INT NOT NULL,
              team_id INT NOT NULL,
              seed INT DEFAULT NULL,
              group_key VARCHAR(50) DEFAULT NULL,
              metadata JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              INDEX idx_tournament_teams_tournament_id (tournament_id),
              INDEX idx_tournament_teams_team_id (team_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_tournaments_tournament_id FOREIGN KEY (tournament_id) REFERENCES tournaments (id) ON DELETE CASCADE
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
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_games_game_id FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_tournament_matches_next_match_id FOREIGN KEY (next_match_id) REFERENCES tournament_matches (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_teams
            ADD
              CONSTRAINT fk_tournament_teams_tournaments_tournament_id FOREIGN KEY (tournament_id) REFERENCES tournaments (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_teams
            ADD
              CONSTRAINT fk_tournament_teams_teams_team_id FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
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
            ALTER TABLE
              tournament_matches
            DROP
              FOREIGN KEY fk_tournament_matches_tournaments_tournament_id
        SQL);
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
            ALTER TABLE tournament_matches DROP FOREIGN KEY fk_tournament_matches_games_game_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            DROP
              FOREIGN KEY fk_tournament_matches_tournament_matches_next_match_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_teams DROP FOREIGN KEY fk_tournament_teams_tournaments_tournament_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_teams DROP FOREIGN KEY fk_tournament_teams_teams_team_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournaments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournament_matches
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tournament_teams
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
