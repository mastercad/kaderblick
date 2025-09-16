<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823102604 extends AbstractMigration
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
              games
            ADD
              fussball_de_id VARCHAR(255) DEFAULT NULL,
            ADD
              fussball_de_url VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_games_fussball_de_id ON games (fussball_de_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              teams
            ADD
              fussball_de_id VARCHAR(255) DEFAULT NULL,
            ADD
              fussball_de_url VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_teams_fussball_de_id ON teams (fussball_de_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              players
            ADD
              fussball_de_id VARCHAR(255) DEFAULT NULL,
            ADD
              fussball_de_url VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_players_fussball_de_id ON players (fussball_de_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              clubs
            ADD
              fussball_de_id VARCHAR(255) DEFAULT NULL,
            ADD
              fussball_de_url VARCHAR(255) DEFAULT NULL,
            ADD
              club_colors VARCHAR(255) DEFAULT NULL,
            ADD
              contact_person VARCHAR(255) DEFAULT NULL,
            ADD
              founding_year INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_clubs_fussball_de_id ON clubs (fussball_de_id)
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
            DROP INDEX uniq_players_fussball_de_id ON players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP fussball_de_id, DROP fussball_de_url
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_clubs_fussball_de_id ON clubs
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              clubs
            DROP
              fussball_de_id,
            DROP
              fussball_de_url,
            DROP
              club_colors,
            DROP
              contact_person,
            DROP
              founding_year
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_games_fussball_de_id ON games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP fussball_de_id, DROP fussball_de_url
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_teams_fussball_de_id ON teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teams DROP fussball_de_id, DROP fussball_de_url
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
