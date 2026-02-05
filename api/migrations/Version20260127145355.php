<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127145355 extends AbstractMigration
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
            ALTER TABLE tournament_matches DROP FOREIGN KEY fk_tournament_matches_games_game_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_games_game_id FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY fk_games_tournament_matches_tournament_match_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_games_tournament_match_id ON games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP tournament_match_id
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
            ALTER TABLE games ADD tournament_match_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              games
            ADD
              CONSTRAINT fk_games_tournament_matches_tournament_match_id FOREIGN KEY (tournament_match_id) REFERENCES tournament_matches (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_games_tournament_match_id ON games (tournament_match_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournament_matches DROP FOREIGN KEY fk_tournament_matches_games_game_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournament_matches
            ADD
              CONSTRAINT fk_tournament_matches_games_game_id FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE
            SET
              NULL
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
