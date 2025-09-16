<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250816075652 extends AbstractMigration
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
            CREATE TABLE player_nationality_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              player_id INT NOT NULL,
              nationality_id INT NOT NULL,
              start_date DATE NOT NULL,
              end_date DATE DEFAULT NULL,
              active TINYINT(1) DEFAULT 1 NOT NULL,
              INDEX idx_player_nationality_assignments_player_id (player_id),
              INDEX idx_player_nationality_assignments_nationality_id (nationality_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_club_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              coach_id INT NOT NULL,
              club_id INT NOT NULL,
              start_date DATE DEFAULT NULL,
              end_date DATE DEFAULT NULL,
              active TINYINT(1) DEFAULT 1 NOT NULL,
              INDEX idx_coach_club_assignments_coach_id (coach_id),
              INDEX idx_coach_club_assignments_club_id (club_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
              id INT AUTO_INCREMENT NOT NULL,
              email VARCHAR(255) NOT NULL,
              first_name VARCHAR(255) NOT NULL,
              last_name VARCHAR(255) NOT NULL,
              roles JSON NOT NULL COMMENT '(DC2Type:json)',
              password VARCHAR(255) NOT NULL,
              is_verified TINYINT(1) NOT NULL,
              is_enabled TINYINT(1) NOT NULL,
              verification_token VARCHAR(255) DEFAULT NULL,
              verification_expires DATETIME DEFAULT NULL,
              height DOUBLE PRECISION DEFAULT NULL,
              weight DOUBLE PRECISION DEFAULT NULL,
              shoe_size DOUBLE PRECISION DEFAULT NULL,
              shirt_size VARCHAR(3) DEFAULT NULL,
              pants_size VARCHAR(10) DEFAULT NULL,
              new_email VARCHAR(180) DEFAULT NULL,
              email_verification_token VARCHAR(100) DEFAULT NULL,
              email_verification_token_expires_at DATETIME DEFAULT NULL,
              UNIQUE INDEX uniq_users_email (email),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_tokens (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              token VARCHAR(64) NOT NULL,
              expires_at DATETIME NOT NULL,
              UNIQUE INDEX uniq_refresh_tokens_token (token),
              INDEX idx_refresh_tokens_user_id (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_events (
              id INT AUTO_INCREMENT NOT NULL,
              game_id INT NOT NULL,
              game_event_type_id INT NOT NULL,
              team_id INT NOT NULL,
              related_player_id INT DEFAULT NULL,
              substitution_reason_id INT DEFAULT NULL,
              timestamp DATETIME NOT NULL,
              description LONGTEXT DEFAULT NULL,
              player_id INT DEFAULT NULL,
              INDEX idx_game_events_game_id (game_id),
              INDEX idx_game_events_game_event_type_id (game_event_type_id),
              INDEX idx_game_events_player_id (player_id),
              INDEX idx_game_events_team_id (team_id),
              INDEX idx_game_events_related_player_id (related_player_id),
              INDEX idx_game_events_substitution_reason_id (substitution_reason_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_nationality_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              coach_id INT NOT NULL,
              nationality_id INT NOT NULL,
              start_date DATE NOT NULL,
              end_date DATE DEFAULT NULL,
              active TINYINT(1) DEFAULT 1 NOT NULL,
              INDEX idx_coach_nationality_assignments_coach_id (coach_id),
              INDEX idx_coach_nationality_assignments_nationality_id (nationality_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (
              id BIGINT AUTO_INCREMENT NOT NULL,
              body LONGTEXT NOT NULL,
              headers LONGTEXT NOT NULL,
              queue_name VARCHAR(255) NOT NULL,
              created_at DATETIME NOT NULL,
              available_at DATETIME NOT NULL,
              delivered_at DATETIME DEFAULT NULL,
              INDEX idx_queue_name (queue_name),
              INDEX idx_available_at (available_at),
              INDEX idx_delivered_at (delivered_at),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE goals (
              id INT AUTO_INCREMENT NOT NULL,
              game_id INT NOT NULL,
              assist_by_id INT DEFAULT NULL,
              minute INT NOT NULL,
              own_goal TINYINT(1) NOT NULL,
              penalty TINYINT(1) NOT NULL,
              scorer_id INT NOT NULL,
              INDEX idx_goals_scorer_id (scorer_id),
              INDEX idx_goals_game_id (game_id),
              INDEX idx_goals_assist_by_id (assist_by_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE permissions (
              id INT AUTO_INCREMENT NOT NULL,
              identifier VARCHAR(50) NOT NULL,
              name VARCHAR(100) NOT NULL,
              description VARCHAR(255) DEFAULT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participation_statuses (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(100) NOT NULL,
              code VARCHAR(50) NOT NULL,
              color VARCHAR(7) DEFAULT NULL,
              icon VARCHAR(50) DEFAULT NULL,
              sort_order INT NOT NULL,
              is_active TINYINT(1) NOT NULL,
              UNIQUE INDEX uniq_participation_statuses_code (code),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_game_stats (
              id INT AUTO_INCREMENT NOT NULL,
              game_id INT NOT NULL,
              team_id INT NOT NULL,
              possession INT DEFAULT NULL,
              corners INT DEFAULT NULL,
              offsides INT DEFAULT NULL,
              shots INT DEFAULT NULL,
              shots_on_target INT DEFAULT NULL,
              fouls INT DEFAULT NULL,
              yellow_cards INT DEFAULT NULL,
              red_cards INT DEFAULT NULL,
              INDEX idx_team_game_stats_game_id (game_id),
              INDEX idx_team_game_stats_team_id (team_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_team_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              player_id INT NOT NULL,
              player_team_assignment_type_id INT DEFAULT NULL,
              team_id INT NOT NULL,
              shirt_number INT DEFAULT NULL,
              start_date DATE DEFAULT NULL,
              end_date DATE DEFAULT NULL,
              INDEX idx_player_team_assignments_player_id (player_id),
              INDEX idx_player_team_assignments_player_team_assignment_type_id (player_team_assignment_type_id),
              INDEX idx_player_team_assignments_team_id (team_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE games (
              id INT AUTO_INCREMENT NOT NULL,
              away_team_id INT NOT NULL,
              game_type_id INT NOT NULL,
              location_id INT DEFAULT NULL,
              calendar_event_id INT DEFAULT NULL,
              home_score INT DEFAULT NULL,
              away_score INT DEFAULT NULL,
              is_finished TINYINT(1) NOT NULL,
              home_team_id INT NOT NULL,
              INDEX idx_games_home_team_id (home_team_id),
              INDEX idx_games_away_team_id (away_team_id),
              INDEX idx_games_game_type_id (game_type_id),
              INDEX idx_games_location_id (location_id),
              UNIQUE INDEX uniq_games_calendar_event_id (calendar_event_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_relations (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              player_id INT DEFAULT NULL,
              coach_id INT DEFAULT NULL,
              relation_type_id INT NOT NULL,
              permissions JSON NOT NULL COMMENT '(DC2Type:json)',
              INDEX idx_user_relations_user_id (user_id),
              INDEX idx_user_relations_player_id (player_id),
              INDEX idx_user_relations_coach_id (coach_id),
              INDEX idx_user_relations_relation_type_id (relation_type_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_license_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              license_id INT NOT NULL,
              coach_id INT NOT NULL,
              start_date DATE NOT NULL,
              end_date DATE DEFAULT NULL,
              active TINYINT(1) NOT NULL,
              INDEX idx_coach_license_assignments_license_id (license_id),
              INDEX idx_coach_license_assignments_coach_id (coach_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE locations (
              id INT AUTO_INCREMENT NOT NULL,
              surface_type_id INT DEFAULT NULL,
              name VARCHAR(100) NOT NULL,
              address VARCHAR(255) DEFAULT NULL,
              city VARCHAR(255) DEFAULT NULL,
              capacity INT DEFAULT NULL,
              has_floodlight TINYINT(1) DEFAULT NULL,
              facilities VARCHAR(255) DEFAULT NULL,
              latitude DOUBLE PRECISION DEFAULT NULL,
              longitude DOUBLE PRECISION DEFAULT NULL,
              INDEX idx_locations_surface_type_id (surface_type_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE news (
              id INT AUTO_INCREMENT NOT NULL,
              club_id INT DEFAULT NULL,
              team_id INT DEFAULT NULL,
              created_by INT NOT NULL,
              title VARCHAR(255) NOT NULL,
              content LONGTEXT NOT NULL,
              visibility VARCHAR(20) NOT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX idx_news_club_id (club_id),
              INDEX idx_news_team_id (team_id),
              INDEX idx_news_created_by (created_by),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE push_subscriptions (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              endpoint VARCHAR(500) NOT NULL,
              public_key VARCHAR(255) NOT NULL,
              auth_token VARCHAR(255) NOT NULL,
              INDEX idx_push_subscriptions_user_id (user_id),
              UNIQUE INDEX uniq_push_endpoint (endpoint),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participations (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              event_id INT NOT NULL,
              status_id INT NOT NULL,
              note LONGTEXT DEFAULT NULL,
              created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX idx_participations_user_id (user_id),
              INDEX idx_participations_event_id (event_id),
              INDEX idx_participations_status_id (status_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE teams (
              id INT AUTO_INCREMENT NOT NULL,
              age_group_id INT NOT NULL,
              league_id INT DEFAULT NULL,
              name VARCHAR(100) NOT NULL,
              INDEX idx_teams_age_group_id (age_group_id),
              INDEX idx_teams_league_id (league_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_club (
              team_id INT NOT NULL,
              club_id INT NOT NULL,
              INDEX idx_team_club_team_id (team_id),
              INDEX idx_team_club_club_id (club_id),
              PRIMARY KEY(team_id, club_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE leagues (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE feedback (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              type VARCHAR(20) NOT NULL,
              message LONGTEXT NOT NULL,
              url VARCHAR(255) NOT NULL,
              user_agent VARCHAR(255) DEFAULT NULL,
              created_at DATETIME NOT NULL,
              is_read TINYINT(1) NOT NULL,
              resolved TINYINT(1) NOT NULL,
              admin_note LONGTEXT DEFAULT NULL,
              screenshot_path VARCHAR(255) DEFAULT NULL,
              INDEX idx_feedback_user_id (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE surface_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(50) NOT NULL,
              description VARCHAR(255) DEFAULT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_team_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              coach_id INT NOT NULL,
              coach_team_assignment_type_id INT DEFAULT NULL,
              team_id INT NOT NULL,
              start_date DATE DEFAULT NULL,
              end_date DATE DEFAULT NULL,
              INDEX idx_coach_team_assignments_coach_id (coach_id),
              INDEX idx_coach_team_assignments_coach_team_assignment_type_id (coach_team_assignment_type_id),
              INDEX idx_coach_team_assignments_team_id (team_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE relation_types (
              id INT AUTO_INCREMENT NOT NULL,
              identifier VARCHAR(50) NOT NULL,
              name VARCHAR(100) NOT NULL,
              category VARCHAR(20) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE players (
              id INT AUTO_INCREMENT NOT NULL,
              strong_foot_id INT DEFAULT NULL,
              main_position_id INT NOT NULL,
              first_name VARCHAR(100) NOT NULL,
              last_name VARCHAR(100) NOT NULL,
              birthdate DATE DEFAULT NULL,
              height INT DEFAULT NULL,
              weight INT DEFAULT NULL,
              email VARCHAR(255) DEFAULT NULL,
              INDEX idx_players_strong_foot_id (strong_foot_id),
              INDEX idx_players_main_position_id (main_position_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_alternative_positions (
              player_id INT NOT NULL,
              position_id INT NOT NULL,
              INDEX idx_player_alternative_positions_player_id (player_id),
              INDEX idx_player_alternative_positions_position_id (position_id),
              PRIMARY KEY(player_id, position_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messages (
              id INT AUTO_INCREMENT NOT NULL,
              sender_id INT NOT NULL,
              subject VARCHAR(255) NOT NULL,
              content LONGTEXT NOT NULL,
              sent_at DATETIME NOT NULL,
              read_by JSON NOT NULL COMMENT '(DC2Type:json)',
              INDEX idx_messages_sender_id (sender_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_recipients (
              message_id INT NOT NULL,
              user_id INT NOT NULL,
              INDEX idx_message_recipients_message_id (message_id),
              INDEX idx_message_recipients_user_id (user_id),
              PRIMARY KEY(message_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_groups (
              id INT AUTO_INCREMENT NOT NULL,
              owner_id INT NOT NULL,
              name VARCHAR(255) NOT NULL,
              INDEX idx_message_groups_owner_id (owner_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_group_members (
              message_group_id INT NOT NULL,
              user_id INT NOT NULL,
              INDEX idx_message_group_members_message_group_id (message_group_id),
              INDEX idx_message_group_members_user_id (user_id),
              PRIMARY KEY(message_group_id, user_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE clubs (
              id INT AUTO_INCREMENT NOT NULL,
              location_id INT DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(100) DEFAULT NULL,
              abbreviation VARCHAR(10) DEFAULT NULL,
              stadium_name VARCHAR(255) DEFAULT NULL,
              logo_url VARCHAR(255) DEFAULT NULL,
              website VARCHAR(255) DEFAULT NULL,
              email VARCHAR(255) DEFAULT NULL,
              phone VARCHAR(20) DEFAULT NULL,
              active TINYINT(1) NOT NULL,
              INDEX idx_clubs_location_id (location_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coaches (
              id INT AUTO_INCREMENT NOT NULL,
              first_name VARCHAR(100) NOT NULL,
              last_name VARCHAR(100) NOT NULL,
              birthdate DATE DEFAULT NULL,
              email VARCHAR(255) DEFAULT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_event_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(50) NOT NULL,
              code VARCHAR(50) NOT NULL,
              color VARCHAR(10) DEFAULT NULL,
              icon VARCHAR(100) DEFAULT NULL,
              is_system TINYINT(1) NOT NULL,
              UNIQUE INDEX uniq_game_event_types_name (name),
              UNIQUE INDEX uniq_game_event_types_code (code),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_team_assignment_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(100) NOT NULL,
              description VARCHAR(100) DEFAULT NULL,
              active TINYINT(1) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE nationalities (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(100) NOT NULL,
              iso_code VARCHAR(3) NOT NULL,
              UNIQUE INDEX uniq_nationalities_iso_code (iso_code),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strong_feet (
              id INT AUTO_INCREMENT NOT NULL,
              code VARCHAR(20) NOT NULL,
              name VARCHAR(50) NOT NULL,
              UNIQUE INDEX uniq_strong_feet_code (code),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE formation_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(50) NOT NULL,
              background_path VARCHAR(255) NOT NULL,
              css_class VARCHAR(100) DEFAULT NULL,
              default_positions JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE calendar_event_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              color VARCHAR(7) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(100) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              UNIQUE INDEX uniq_game_types_name (name),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE substitutions (
              id INT AUTO_INCREMENT NOT NULL,
              game_id INT NOT NULL,
              player_out_id INT NOT NULL,
              team_id INT NOT NULL,
              substitution_reason_id INT DEFAULT NULL,
              minute INT NOT NULL,
              player_in_id INT NOT NULL,
              INDEX idx_substitutions_game_id (game_id),
              INDEX idx_substitutions_player_in_id (player_in_id),
              INDEX idx_substitutions_player_out_id (player_out_id),
              INDEX idx_substitutions_team_id (team_id),
              INDEX idx_substitutions_substitution_reason_id (substitution_reason_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_club_assignments (
              id INT AUTO_INCREMENT NOT NULL,
              player_id INT NOT NULL,
              club_id INT NOT NULL,
              start_date DATE DEFAULT NULL,
              end_date DATE DEFAULT NULL,
              active TINYINT(1) DEFAULT 1 NOT NULL,
              INDEX idx_player_club_assignments_player_id (player_id),
              INDEX idx_player_club_assignments_club_id (club_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_game_stats (
              id INT AUTO_INCREMENT NOT NULL,
              game_id INT NOT NULL,
              player_id INT NOT NULL,
              minutes_played INT DEFAULT NULL,
              shots INT DEFAULT NULL,
              shots_on_target INT DEFAULT NULL,
              passes INT DEFAULT NULL,
              passes_completed INT DEFAULT NULL,
              tackles INT DEFAULT NULL,
              interceptions INT DEFAULT NULL,
              fouls_committed INT DEFAULT NULL,
              fouls_suffered INT DEFAULT NULL,
              distance_covered INT DEFAULT NULL,
              INDEX idx_player_game_stats_game_id (game_id),
              INDEX idx_player_game_stats_player_id (player_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_team_assignment_types (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(100) NOT NULL,
              description VARCHAR(100) DEFAULT NULL,
              active TINYINT(1) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE substitution_reasons (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              active TINYINT(1) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE age_groups (
              id INT AUTO_INCREMENT NOT NULL,
              code VARCHAR(50) NOT NULL,
              name VARCHAR(100) NOT NULL,
              english_name VARCHAR(100) NOT NULL,
              min_age INT NOT NULL,
              max_age INT NOT NULL,
              reference_date VARCHAR(5) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              UNIQUE INDEX uniq_age_groups_code (code),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE calendar_events (
              id INT AUTO_INCREMENT NOT NULL,
              calendar_event_type_id INT DEFAULT NULL,
              location_id INT DEFAULT NULL,
              title VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              start_date DATETIME NOT NULL,
              end_date DATETIME DEFAULT NULL,
              notification_sent TINYINT(1) NOT NULL,
              INDEX idx_calendar_events_calendar_event_type_id (calendar_event_type_id),
              INDEX idx_calendar_events_location_id (location_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE dashboard_widgets (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT NOT NULL,
              type VARCHAR(50) NOT NULL,
              config JSON NOT NULL COMMENT '(DC2Type:json)',
              position INT NOT NULL,
              width INT NOT NULL,
              enabled TINYINT(1) NOT NULL,
              INDEX idx_dashboard_widgets_user_id (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE formations (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT DEFAULT NULL,
              formation_type_id INT DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              formation_data JSON NOT NULL COMMENT '(DC2Type:json)',
              INDEX idx_formations_user_id (user_id),
              INDEX idx_formations_formation_type_id (formation_type_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE positions (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(50) NOT NULL,
              short_name VARCHAR(10) DEFAULT NULL,
              description VARCHAR(255) DEFAULT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_licenses (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              country_code VARCHAR(100) DEFAULT NULL,
              active TINYINT(1) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_nationality_assignments
            ADD
              CONSTRAINT fk_player_nationality_assignments_players FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_nationality_assignments
            ADD
              CONSTRAINT fk_player_nationality_assignments_nationalities FOREIGN KEY (nationality_id) REFERENCES nationalities (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_club_assignments
            ADD
              CONSTRAINT fk_coach_club_assignments_coaches FOREIGN KEY (coach_id) REFERENCES coaches (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_club_assignments
            ADD
              CONSTRAINT fk_coach_club_assignments_clubs FOREIGN KEY (club_id) REFERENCES clubs (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              refresh_tokens
            ADD
              CONSTRAINT fk_refresh_tokens_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              game_events
            ADD
              CONSTRAINT fk_game_events_games FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              game_events
            ADD
              CONSTRAINT fk_game_events_game_event_types FOREIGN KEY (game_event_type_id) REFERENCES game_event_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              game_events
            ADD
              CONSTRAINT fk_game_events_players FOREIGN KEY (related_player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              game_events
            ADD
              CONSTRAINT fk_game_events_teams FOREIGN KEY (team_id) REFERENCES teams (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              game_events
            ADD
              CONSTRAINT fk_game_events_substitution_reasons FOREIGN KEY (substitution_reason_id) REFERENCES substitution_reasons (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_nationality_assignments
            ADD
              CONSTRAINT fk_coach_nationality_assignments_coaches FOREIGN KEY (coach_id) REFERENCES coaches (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_nationality_assignments
            ADD
              CONSTRAINT fk_coach_nationality_assignments_nationalities FOREIGN KEY (nationality_id) REFERENCES nationalities (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              goals
            ADD
              CONSTRAINT fk_goals_players FOREIGN KEY (assist_by_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals ADD CONSTRAINT fk_goals_games FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_game_stats
            ADD
              CONSTRAINT fk_team_game_stats_games FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_game_stats
            ADD
              CONSTRAINT fk_team_game_stats_teams FOREIGN KEY (team_id) REFERENCES teams (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_team_assignments
            ADD
              CONSTRAINT fk_player_team_assignments_players FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_team_assignments
            ADD
              CONSTRAINT fk_player_team_assignments_player_team_assignment_types FOREIGN KEY (player_team_assignment_type_id) REFERENCES player_team_assignment_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_team_assignments
            ADD
              CONSTRAINT fk_player_team_assignments_teams FOREIGN KEY (team_id) REFERENCES teams (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              games
            ADD
              CONSTRAINT fk_games_teams FOREIGN KEY (away_team_id) REFERENCES teams (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              games
            ADD
              CONSTRAINT fk_games_game_types FOREIGN KEY (game_type_id) REFERENCES game_types (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              games
            ADD
              CONSTRAINT fk_games_locations FOREIGN KEY (location_id) REFERENCES locations (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              games
            ADD
              CONSTRAINT fk_games_calendar_events FOREIGN KEY (calendar_event_id) REFERENCES calendar_events (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_relations
            ADD
              CONSTRAINT fk_user_relations_users FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_relations
            ADD
              CONSTRAINT fk_user_relations_players FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_relations
            ADD
              CONSTRAINT fk_user_relations_coaches FOREIGN KEY (coach_id) REFERENCES coaches (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_relations
            ADD
              CONSTRAINT fk_user_relations_relation_types FOREIGN KEY (relation_type_id) REFERENCES relation_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_license_assignments
            ADD
              CONSTRAINT fk_coach_license_assignments_coach_licenses FOREIGN KEY (license_id) REFERENCES coach_licenses (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_license_assignments
            ADD
              CONSTRAINT fk_coach_license_assignments_coaches FOREIGN KEY (coach_id) REFERENCES coaches (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              locations
            ADD
              CONSTRAINT fk_locations_surface_types FOREIGN KEY (surface_type_id) REFERENCES surface_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              news
            ADD
              CONSTRAINT fk_news_clubs FOREIGN KEY (club_id) REFERENCES clubs (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              news
            ADD
              CONSTRAINT fk_news_teams FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE news ADD CONSTRAINT fk_news_users FOREIGN KEY (created_by) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              push_subscriptions
            ADD
              CONSTRAINT fk_push_subscriptions_users FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              participations
            ADD
              CONSTRAINT fk_participations_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              participations
            ADD
              CONSTRAINT fk_participations_calendar_events FOREIGN KEY (event_id) REFERENCES calendar_events (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              participations
            ADD
              CONSTRAINT fk_participations_participation_statuses FOREIGN KEY (status_id) REFERENCES participation_statuses (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              teams
            ADD
              CONSTRAINT fk_teams_age_groups FOREIGN KEY (age_group_id) REFERENCES age_groups (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              teams
            ADD
              CONSTRAINT fk_teams_leagues FOREIGN KEY (league_id) REFERENCES leagues (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_club
            ADD
              CONSTRAINT fk_team_club_teams FOREIGN KEY (team_id) REFERENCES teams (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              team_club
            ADD
              CONSTRAINT fk_team_club_clubs FOREIGN KEY (club_id) REFERENCES clubs (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              feedback
            ADD
              CONSTRAINT fk_feedback_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_team_assignments
            ADD
              CONSTRAINT fk_coach_team_assignments_coaches FOREIGN KEY (coach_id) REFERENCES coaches (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_team_assignments
            ADD
              CONSTRAINT fk_coach_team_assignments_coach_team_assignment_types FOREIGN KEY (coach_team_assignment_type_id) REFERENCES coach_team_assignment_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_team_assignments
            ADD
              CONSTRAINT fk_coach_team_assignments_teams FOREIGN KEY (team_id) REFERENCES teams (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              players
            ADD
              CONSTRAINT fk_players_strong_feet FOREIGN KEY (strong_foot_id) REFERENCES strong_feet (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              players
            ADD
              CONSTRAINT fk_players_positions FOREIGN KEY (main_position_id) REFERENCES positions (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_alternative_positions
            ADD
              CONSTRAINT fk_player_alternative_positions_players FOREIGN KEY (player_id) REFERENCES players (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_alternative_positions
            ADD
              CONSTRAINT fk_player_alternative_positions_positions FOREIGN KEY (position_id) REFERENCES positions (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              messages
            ADD
              CONSTRAINT fk_messages_users FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              message_recipients
            ADD
              CONSTRAINT fk_message_recipients_messages FOREIGN KEY (message_id) REFERENCES messages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              message_recipients
            ADD
              CONSTRAINT fk_message_recipients_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              message_groups
            ADD
              CONSTRAINT fk_message_groups_users FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              message_group_members
            ADD
              CONSTRAINT fk_message_group_members_message_groups FOREIGN KEY (message_group_id) REFERENCES message_groups (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              message_group_members
            ADD
              CONSTRAINT fk_message_group_members_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              clubs
            ADD
              CONSTRAINT fk_clubs_locations FOREIGN KEY (location_id) REFERENCES locations (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              substitutions
            ADD
              CONSTRAINT fk_substitutions_games FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              substitutions
            ADD
              CONSTRAINT fk_substitutions_players FOREIGN KEY (player_out_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              substitutions
            ADD
              CONSTRAINT fk_substitutions_teams FOREIGN KEY (team_id) REFERENCES teams (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              substitutions
            ADD
              CONSTRAINT fk_substitutions_substitution_reasons FOREIGN KEY (substitution_reason_id) REFERENCES substitution_reasons (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_club_assignments
            ADD
              CONSTRAINT fk_player_club_assignments_players FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_club_assignments
            ADD
              CONSTRAINT fk_player_club_assignments_clubs FOREIGN KEY (club_id) REFERENCES clubs (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_game_stats
            ADD
              CONSTRAINT fk_player_game_stats_games FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_game_stats
            ADD
              CONSTRAINT fk_player_game_stats_players FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              calendar_events
            ADD
              CONSTRAINT fk_calendar_events_calendar_event_types FOREIGN KEY (calendar_event_type_id) REFERENCES calendar_event_types (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              calendar_events
            ADD
              CONSTRAINT fk_calendar_events_locations FOREIGN KEY (location_id) REFERENCES locations (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              dashboard_widgets
            ADD
              CONSTRAINT fk_dashboard_widgets_users FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              formations
            ADD
              CONSTRAINT fk_formations_users FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              formations
            ADD
              CONSTRAINT fk_formations_formation_types FOREIGN KEY (formation_type_id) REFERENCES formation_types (id) ON DELETE
            SET
              NULL
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
              player_nationality_assignments
            DROP
              FOREIGN KEY fk_player_nationality_assignments_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_nationality_assignments
            DROP
              FOREIGN KEY fk_player_nationality_assignments_nationalities
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignments DROP FOREIGN KEY fk_coach_club_assignments_coaches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignments DROP FOREIGN KEY fk_coach_club_assignments_clubs
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_tokens DROP FOREIGN KEY fk_refresh_tokens_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY fk_game_events_games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY fk_game_events_game_event_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY fk_game_events_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY fk_game_events_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY fk_game_events_substitution_reasons
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_nationality_assignments
            DROP
              FOREIGN KEY fk_coach_nationality_assignments_coaches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_nationality_assignments
            DROP
              FOREIGN KEY fk_coach_nationality_assignments_nationalities
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY fk_goals_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY fk_goals_games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY fk_team_game_stats_games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY fk_team_game_stats_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignments DROP FOREIGN KEY fk_player_team_assignments_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_team_assignments
            DROP
              FOREIGN KEY fk_player_team_assignments_player_team_assignment_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignments DROP FOREIGN KEY fk_player_team_assignments_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY fk_games_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY fk_games_game_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY fk_games_locations
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY fk_games_calendar_events
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY fk_user_relations_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY fk_user_relations_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY fk_user_relations_coaches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_relations DROP FOREIGN KEY fk_user_relations_relation_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_license_assignments
            DROP
              FOREIGN KEY fk_coach_license_assignments_coach_licenses
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignments DROP FOREIGN KEY fk_coach_license_assignments_coaches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE locations DROP FOREIGN KEY fk_locations_surface_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE news DROP FOREIGN KEY fk_news_clubs
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE news DROP FOREIGN KEY fk_news_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE news DROP FOREIGN KEY fk_news_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE push_subscriptions DROP FOREIGN KEY fk_push_subscriptions_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations DROP FOREIGN KEY fk_participations_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations DROP FOREIGN KEY fk_participations_calendar_events
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations DROP FOREIGN KEY fk_participations_participation_statuses
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teams DROP FOREIGN KEY fk_teams_age_groups
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teams DROP FOREIGN KEY fk_teams_leagues
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club DROP FOREIGN KEY fk_team_club_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club DROP FOREIGN KEY fk_team_club_clubs
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feedback DROP FOREIGN KEY fk_feedback_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignments DROP FOREIGN KEY fk_coach_team_assignments_coaches
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              coach_team_assignments
            DROP
              FOREIGN KEY fk_coach_team_assignments_coach_team_assignment_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignments DROP FOREIGN KEY fk_coach_team_assignments_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP FOREIGN KEY fk_players_strong_feet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP FOREIGN KEY fk_players_positions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_alternative_positions
            DROP
              FOREIGN KEY fk_player_alternative_positions_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              player_alternative_positions
            DROP
              FOREIGN KEY fk_player_alternative_positions_positions
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY fk_messages_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_recipients DROP FOREIGN KEY fk_message_recipients_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_recipients DROP FOREIGN KEY fk_message_recipients_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_groups DROP FOREIGN KEY fk_message_groups_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_group_members DROP FOREIGN KEY fk_message_group_members_message_groups
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_group_members DROP FOREIGN KEY fk_message_group_members_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clubs DROP FOREIGN KEY fk_clubs_locations
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitutions DROP FOREIGN KEY fk_substitutions_games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitutions DROP FOREIGN KEY fk_substitutions_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitutions DROP FOREIGN KEY fk_substitutions_teams
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitutions DROP FOREIGN KEY fk_substitutions_substitution_reasons
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignments DROP FOREIGN KEY fk_player_club_assignments_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignments DROP FOREIGN KEY fk_player_club_assignments_clubs
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats DROP FOREIGN KEY fk_player_game_stats_games
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats DROP FOREIGN KEY fk_player_game_stats_players
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY fk_calendar_events_calendar_event_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY fk_calendar_events_locations
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE dashboard_widgets DROP FOREIGN KEY fk_dashboard_widgets_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formations DROP FOREIGN KEY fk_formations_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formations DROP FOREIGN KEY fk_formations_formation_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_nationality_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_club_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_nationality_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE goals
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE permissions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participation_statuses
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_game_stats
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_team_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE games
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_relations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_license_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE locations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE news
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE push_subscriptions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE teams
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_club
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE leagues
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE feedback
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE surface_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_team_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE relation_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE players
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_alternative_positions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_recipients
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_groups
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_group_members
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE clubs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coaches
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_event_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_team_assignment_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE nationalities
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strong_feet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE formation_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar_event_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE substitutions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_club_assignments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_game_stats
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_team_assignment_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE substitution_reasons
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE age_groups
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar_events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE dashboard_widgets
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE formations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE positions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_licenses
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
