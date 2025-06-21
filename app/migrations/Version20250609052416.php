<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609052416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE age_groups (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, english_name VARCHAR(100) NOT NULL, min_age INT NOT NULL, max_age INT NOT NULL, reference_date VARCHAR(5) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_E994440777153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE clubs (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(100) DEFAULT NULL, abbreviation VARCHAR(10) DEFAULT NULL, stadium_name VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, active TINYINT(1) NOT NULL, INDEX IDX_A5BD312364D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_club_assignment (id INT AUTO_INCREMENT NOT NULL, coach_id INT NOT NULL, club_id INT NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_C61D1E553C105691 (coach_id), INDEX IDX_C61D1E5561190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_license (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, country_code VARCHAR(100) DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_license_assignment (id INT AUTO_INCREMENT NOT NULL, license_id INT NOT NULL, coach_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, active TINYINT(1) NOT NULL, INDEX IDX_F0EF85EB460F904B (license_id), INDEX IDX_F0EF85EB3C105691 (coach_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_nationality_assignment (id INT AUTO_INCREMENT NOT NULL, coach_id INT NOT NULL, nationality_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_16FBED593C105691 (coach_id), INDEX IDX_16FBED591C9DA55 (nationality_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_team_assignment (id INT AUTO_INCREMENT NOT NULL, coach_id INT NOT NULL, coach_team_assignment_type_id INT DEFAULT NULL, team_id INT NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, INDEX IDX_B102227D3C105691 (coach_id), INDEX IDX_B102227DBE1C41B5 (coach_team_assignment_type_id), INDEX IDX_B102227D296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE coach_team_assignment_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(100) DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, home_team_id INT DEFAULT NULL, away_team_id INT DEFAULT NULL, game_type_id INT NOT NULL, location_id INT DEFAULT NULL, date DATETIME DEFAULT NULL, home_score INT DEFAULT NULL, away_score INT DEFAULT NULL, is_finished TINYINT(1) NOT NULL, INDEX IDX_232B318C9C4C13F6 (home_team_id), INDEX IDX_232B318C45185D02 (away_team_id), INDEX IDX_232B318C508EF3BC (game_type_id), INDEX IDX_232B318C64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_event_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, code VARCHAR(50) NOT NULL, color VARCHAR(10) DEFAULT NULL, icon VARCHAR(100) DEFAULT NULL, is_system TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_7DC9DC6F5E237E06 (name), UNIQUE INDEX UNIQ_7DC9DC6F77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_events (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, game_event_type_id INT NOT NULL, player_id INT DEFAULT NULL, team_id INT NOT NULL, related_player_id INT DEFAULT NULL, substitution_reason_id INT DEFAULT NULL, timestamp DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_2EB2FA82E48FD905 (game_id), INDEX IDX_2EB2FA82EAFFB957 (game_event_type_id), INDEX IDX_2EB2FA8299E6F5DF (player_id), INDEX IDX_2EB2FA82296CD8AE (team_id), INDEX IDX_2EB2FA823127A9C4 (related_player_id), INDEX IDX_2EB2FA82DAB8CE79 (substitution_reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_67CB3B055E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE goals (id INT AUTO_INCREMENT NOT NULL, scorer_id INT NOT NULL, game_id INT NOT NULL, assist_by_id INT DEFAULT NULL, minute INT NOT NULL, own_goal TINYINT(1) NOT NULL, penalty TINYINT(1) NOT NULL, INDEX IDX_C7241E2F43B35028 (scorer_id), INDEX IDX_C7241E2FE48FD905 (game_id), INDEX IDX_C7241E2FE9B9D4EE (assist_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, surface_type_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, capacity INT DEFAULT NULL, has_floodlight TINYINT(1) DEFAULT NULL, facilities VARCHAR(255) DEFAULT NULL, INDEX IDX_5E9E89CBDAA1EEDA (surface_type_id), UNIQUE INDEX unique_location_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX idx_queue_name (queue_name), INDEX idx_available_at (available_at), INDEX idx_delivered_at (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE nationality (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, iso_code VARCHAR(3) NOT NULL, UNIQUE INDEX UNIQ_8AC58B7062B6A45E (iso_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_club_assignment (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, club_id INT NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_A019891C99E6F5DF (player_id), INDEX IDX_A019891C61190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_game_stats (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, player_id INT NOT NULL, minutes_played INT DEFAULT NULL, shots INT DEFAULT NULL, shots_on_target INT DEFAULT NULL, passes INT DEFAULT NULL, passes_completed INT DEFAULT NULL, tackles INT DEFAULT NULL, interceptions INT DEFAULT NULL, fouls_committed INT DEFAULT NULL, fouls_suffered INT DEFAULT NULL, distance_covered INT DEFAULT NULL, INDEX IDX_3AD19A18E48FD905 (game_id), INDEX IDX_3AD19A1899E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_nationality_assignment (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, nationality_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_2CC6F51799E6F5DF (player_id), INDEX IDX_2CC6F5171C9DA55 (nationality_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_team_assignment (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, player_team_assignment_type_id INT DEFAULT NULL, team_id INT NOT NULL, shirt_number INT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, INDEX IDX_D706B53499E6F5DF (player_id), INDEX IDX_D706B5342F42103A (player_team_assignment_type_id), INDEX IDX_D706B534296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_team_assignment_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(100) DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE players (id INT AUTO_INCREMENT NOT NULL, strong_foot_id INT DEFAULT NULL, main_position_id INT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, birthdate DATE DEFAULT NULL, height INT DEFAULT NULL, weight INT DEFAULT NULL, INDEX IDX_264E43A63A74B23F (strong_foot_id), INDEX IDX_264E43A6907E6D0 (main_position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE player_alternative_positions (player_id INT NOT NULL, position_id INT NOT NULL, INDEX IDX_47210A0299E6F5DF (player_id), INDEX IDX_47210A02DD842E46 (position_id), PRIMARY KEY(player_id, position_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE position (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, short_name VARCHAR(10) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX unique_position_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C74F21955F37A13B (token), INDEX IDX_C74F2195A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strong_foot (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_B88B4F7777153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE substitution (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, player_in_id INT DEFAULT NULL, player_out_id INT DEFAULT NULL, team_id INT DEFAULT NULL, substitution_reason_id INT DEFAULT NULL, minute INT NOT NULL, INDEX IDX_C7C90AE0E48FD905 (game_id), INDEX IDX_C7C90AE0AF8B0575 (player_in_id), INDEX IDX_C7C90AE09B0FCEA4 (player_out_id), INDEX IDX_C7C90AE0296CD8AE (team_id), INDEX IDX_C7C90AE0DAB8CE79 (substitution_reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE substitution_reason (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE surface_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX unique_surface_type_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, age_group_id INT NOT NULL, name VARCHAR(100) NOT NULL, INDEX IDX_C4E0A61FB09E220E (age_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_club (team_id INT NOT NULL, club_id INT NOT NULL, INDEX IDX_690FCC09296CD8AE (team_id), INDEX IDX_690FCC0961190A32 (club_id), PRIMARY KEY(team_id, club_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_game_stats (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, team_id INT NOT NULL, possession INT DEFAULT NULL, corners INT DEFAULT NULL, offsides INT DEFAULT NULL, shots INT DEFAULT NULL, shots_on_target INT DEFAULT NULL, fouls INT DEFAULT NULL, yellow_cards INT DEFAULT NULL, red_cards INT DEFAULT NULL, INDEX IDX_5BAD6CE0E48FD905 (game_id), INDEX IDX_5BAD6CE0296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, verification_token VARCHAR(255) DEFAULT NULL, verification_expires DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clubs ADD CONSTRAINT FK_A5BD312364D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignment ADD CONSTRAINT FK_C61D1E553C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignment ADD CONSTRAINT FK_C61D1E5561190A32 FOREIGN KEY (club_id) REFERENCES clubs (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignment ADD CONSTRAINT FK_F0EF85EB460F904B FOREIGN KEY (license_id) REFERENCES coach_license (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignment ADD CONSTRAINT FK_F0EF85EB3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_nationality_assignment ADD CONSTRAINT FK_16FBED593C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_nationality_assignment ADD CONSTRAINT FK_16FBED591C9DA55 FOREIGN KEY (nationality_id) REFERENCES nationality (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignment ADD CONSTRAINT FK_B102227D3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignment ADD CONSTRAINT FK_B102227DBE1C41B5 FOREIGN KEY (coach_team_assignment_type_id) REFERENCES coach_team_assignment_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignment ADD CONSTRAINT FK_B102227D296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game ADD CONSTRAINT FK_232B318C9C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game ADD CONSTRAINT FK_232B318C45185D02 FOREIGN KEY (away_team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game ADD CONSTRAINT FK_232B318C508EF3BC FOREIGN KEY (game_type_id) REFERENCES game_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game ADD CONSTRAINT FK_232B318C64D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA82E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA82EAFFB957 FOREIGN KEY (game_event_type_id) REFERENCES game_event_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA8299E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA82296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA823127A9C4 FOREIGN KEY (related_player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA82DAB8CE79 FOREIGN KEY (substitution_reason_id) REFERENCES substitution_reason (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals ADD CONSTRAINT FK_C7241E2F43B35028 FOREIGN KEY (scorer_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals ADD CONSTRAINT FK_C7241E2FE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals ADD CONSTRAINT FK_C7241E2FE9B9D4EE FOREIGN KEY (assist_by_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBDAA1EEDA FOREIGN KEY (surface_type_id) REFERENCES surface_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignment ADD CONSTRAINT FK_A019891C99E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignment ADD CONSTRAINT FK_A019891C61190A32 FOREIGN KEY (club_id) REFERENCES clubs (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats ADD CONSTRAINT FK_3AD19A18E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats ADD CONSTRAINT FK_3AD19A1899E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_nationality_assignment ADD CONSTRAINT FK_2CC6F51799E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_nationality_assignment ADD CONSTRAINT FK_2CC6F5171C9DA55 FOREIGN KEY (nationality_id) REFERENCES nationality (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignment ADD CONSTRAINT FK_D706B53499E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignment ADD CONSTRAINT FK_D706B5342F42103A FOREIGN KEY (player_team_assignment_type_id) REFERENCES player_team_assignment_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignment ADD CONSTRAINT FK_D706B534296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players ADD CONSTRAINT FK_264E43A63A74B23F FOREIGN KEY (strong_foot_id) REFERENCES strong_foot (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players ADD CONSTRAINT FK_264E43A6907E6D0 FOREIGN KEY (main_position_id) REFERENCES position (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_alternative_positions ADD CONSTRAINT FK_47210A0299E6F5DF FOREIGN KEY (player_id) REFERENCES players (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_alternative_positions ADD CONSTRAINT FK_47210A02DD842E46 FOREIGN KEY (position_id) REFERENCES position (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE0E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE0AF8B0575 FOREIGN KEY (player_in_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE09B0FCEA4 FOREIGN KEY (player_out_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE0296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE0DAB8CE79 FOREIGN KEY (substitution_reason_id) REFERENCES substitution_reason (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FB09E220E FOREIGN KEY (age_group_id) REFERENCES age_groups (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club ADD CONSTRAINT FK_690FCC09296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club ADD CONSTRAINT FK_690FCC0961190A32 FOREIGN KEY (club_id) REFERENCES clubs (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats ADD CONSTRAINT FK_5BAD6CE0E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats ADD CONSTRAINT FK_5BAD6CE0296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE clubs DROP FOREIGN KEY FK_A5BD312364D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignment DROP FOREIGN KEY FK_C61D1E553C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignment DROP FOREIGN KEY FK_C61D1E5561190A32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignment DROP FOREIGN KEY FK_F0EF85EB460F904B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignment DROP FOREIGN KEY FK_F0EF85EB3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_nationality_assignment DROP FOREIGN KEY FK_16FBED593C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_nationality_assignment DROP FOREIGN KEY FK_16FBED591C9DA55
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignment DROP FOREIGN KEY FK_B102227D3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignment DROP FOREIGN KEY FK_B102227DBE1C41B5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_team_assignment DROP FOREIGN KEY FK_B102227D296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game DROP FOREIGN KEY FK_232B318C9C4C13F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game DROP FOREIGN KEY FK_232B318C45185D02
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game DROP FOREIGN KEY FK_232B318C508EF3BC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game DROP FOREIGN KEY FK_232B318C64D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA82E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA82EAFFB957
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA8299E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA82296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA823127A9C4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA82DAB8CE79
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY FK_C7241E2F43B35028
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY FK_C7241E2FE48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY FK_C7241E2FE9B9D4EE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBDAA1EEDA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignment DROP FOREIGN KEY FK_A019891C99E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignment DROP FOREIGN KEY FK_A019891C61190A32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats DROP FOREIGN KEY FK_3AD19A18E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats DROP FOREIGN KEY FK_3AD19A1899E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_nationality_assignment DROP FOREIGN KEY FK_2CC6F51799E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_nationality_assignment DROP FOREIGN KEY FK_2CC6F5171C9DA55
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignment DROP FOREIGN KEY FK_D706B53499E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignment DROP FOREIGN KEY FK_D706B5342F42103A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_team_assignment DROP FOREIGN KEY FK_D706B534296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP FOREIGN KEY FK_264E43A63A74B23F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP FOREIGN KEY FK_264E43A6907E6D0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_alternative_positions DROP FOREIGN KEY FK_47210A0299E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_alternative_positions DROP FOREIGN KEY FK_47210A02DD842E46
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE0E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE0AF8B0575
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE09B0FCEA4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE0296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE0DAB8CE79
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FB09E220E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club DROP FOREIGN KEY FK_690FCC09296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club DROP FOREIGN KEY FK_690FCC0961190A32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY FK_5BAD6CE0E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY FK_5BAD6CE0296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE age_groups
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE clubs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_club_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_license
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_license_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_nationality_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_team_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE coach_team_assignment_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_event_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE goals
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE location
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE nationality
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_club_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_game_stats
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_nationality_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_team_assignment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_team_assignment_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE players
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE player_alternative_positions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE position
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strong_foot
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE substitution
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE substitution_reason
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE surface_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_club
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_game_stats
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
    }
}
