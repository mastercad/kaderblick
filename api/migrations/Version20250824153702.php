<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250824153702 extends AbstractMigration
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
            CREATE TABLE videos (
              id INT AUTO_INCREMENT NOT NULL,
              created_from_id INT NOT NULL,
              game_id INT NOT NULL,
              updated_from_id INT DEFAULT NULL,
              video_type_id INT NOT NULL,
              camera_id INT DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              file_path VARCHAR(255) DEFAULT NULL,
              url VARCHAR(255) DEFAULT NULL,
              game_start INT DEFAULT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              youtube_id VARCHAR(255) DEFAULT NULL,
              sort INT DEFAULT NULL,
              length INT NOT NULL,
              INDEX idx_videos_created_from_id (created_from_id),
              INDEX idx_videos_game_id (game_id),
              INDEX idx_videos_updated_from_id (updated_from_id),
              INDEX idx_videos_video_type_id (video_type_id),
              INDEX idx_videos_camera_id (camera_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE camera (
              id INT AUTO_INCREMENT NOT NULL,
              created_from_id INT NOT NULL,
              updated_from_id INT DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX idx_camera_created_from_id (created_from_id),
              INDEX idx_camera_updated_from_id (updated_from_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE video_types (
              id INT AUTO_INCREMENT NOT NULL,
              created_from_id INT NOT NULL,
              updated_from_id INT NOT NULL,
              name VARCHAR(255) NOT NULL,
              sort INT NOT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX idx_video_types_created_from_id (created_from_id),
              INDEX idx_video_types_updated_from_id (updated_from_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              videos
            ADD
              CONSTRAINT fk_videos_users_created_from_id FOREIGN KEY (created_from_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              videos
            ADD
              CONSTRAINT fk_videos_games_game_id FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              videos
            ADD
              CONSTRAINT fk_videos_users_updated_from_id FOREIGN KEY (updated_from_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              videos
            ADD
              CONSTRAINT fk_videos_video_types_video_type_id FOREIGN KEY (video_type_id) REFERENCES video_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              videos
            ADD
              CONSTRAINT fk_videos_camera_camera_id FOREIGN KEY (camera_id) REFERENCES camera (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              camera
            ADD
              CONSTRAINT fk_camera_users_created_from_id FOREIGN KEY (created_from_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              camera
            ADD
              CONSTRAINT fk_camera_users_updated_from_id FOREIGN KEY (updated_from_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              video_types
            ADD
              CONSTRAINT fk_video_types_users_created_from_id FOREIGN KEY (created_from_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              video_types
            ADD
              CONSTRAINT fk_video_types_users_updated_from_id FOREIGN KEY (updated_from_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              game_events
            ADD
              CONSTRAINT fk_game_events_players_player_id FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              goals
            ADD
              CONSTRAINT fk_goals_players_scorer_id FOREIGN KEY (scorer_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              games
            ADD
              CONSTRAINT fk_games_teams_home_team_id FOREIGN KEY (home_team_id) REFERENCES teams (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              substitutions
            ADD
              CONSTRAINT fk_substitutions_players_player_in_id FOREIGN KEY (player_in_id) REFERENCES players (id)
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
            ALTER TABLE videos DROP FOREIGN KEY fk_videos_users_created_from_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE videos DROP FOREIGN KEY fk_videos_games_game_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE videos DROP FOREIGN KEY fk_videos_users_updated_from_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE videos DROP FOREIGN KEY fk_videos_video_types_video_type_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE videos DROP FOREIGN KEY fk_videos_camera_camera_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE camera DROP FOREIGN KEY fk_camera_users_created_from_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE camera DROP FOREIGN KEY fk_camera_users_updated_from_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_types DROP FOREIGN KEY fk_video_types_users_created_from_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_types DROP FOREIGN KEY fk_video_types_users_updated_from_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE videos
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE camera
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE video_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY fk_game_events_players_player_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY fk_games_teams_home_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY fk_goals_players_scorer_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitutions DROP FOREIGN KEY fk_substitutions_players_player_in_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
