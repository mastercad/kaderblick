<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626181724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE games (id INT AUTO_INCREMENT NOT NULL, home_team_id INT DEFAULT NULL, away_team_id INT DEFAULT NULL, game_type_id INT NOT NULL, location_id INT DEFAULT NULL, calendar_event_id INT DEFAULT NULL, date DATETIME DEFAULT NULL, home_score INT DEFAULT NULL, away_score INT DEFAULT NULL, is_finished TINYINT(1) NOT NULL, INDEX IDX_FF232B319C4C13F6 (home_team_id), INDEX IDX_FF232B3145185D02 (away_team_id), INDEX IDX_FF232B31508EF3BC (game_type_id), INDEX IDX_FF232B3164D218E (location_id), UNIQUE INDEX UNIQ_FF232B317495C8E3 (calendar_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_groups (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C74AA47F7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_group_members (message_group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8A0DC08BF7721D56 (message_group_id), INDEX IDX_8A0DC08BA76ED395 (user_id), PRIMARY KEY(message_group_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, read_by JSON NOT NULL COMMENT '(DC2Type:json)', INDEX IDX_DB021E96F624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message_recipients (message_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DBB61E5B537A1329 (message_id), INDEX IDX_DBB61E5BA76ED395 (user_id), PRIMARY KEY(message_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD CONSTRAINT FK_FF232B319C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD CONSTRAINT FK_FF232B3145185D02 FOREIGN KEY (away_team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD CONSTRAINT FK_FF232B31508EF3BC FOREIGN KEY (game_type_id) REFERENCES game_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD CONSTRAINT FK_FF232B3164D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD CONSTRAINT FK_FF232B317495C8E3 FOREIGN KEY (calendar_event_id) REFERENCES calendar_events (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_groups ADD CONSTRAINT FK_C74AA47F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_group_members ADD CONSTRAINT FK_8A0DC08BF7721D56 FOREIGN KEY (message_group_id) REFERENCES message_groups (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_group_members ADD CONSTRAINT FK_8A0DC08BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_recipients ADD CONSTRAINT FK_DBB61E5B537A1329 FOREIGN KEY (message_id) REFERENCES messages (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_recipients ADD CONSTRAINT FK_DBB61E5BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F9E14F16C54C8C93 ON calendar_events
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP type_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F167F4F5D85 FOREIGN KEY (calendar_event_type_id) REFERENCES calendar_event_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F1664D218E FOREIGN KEY (location_id) REFERENCES location (id)
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
            ALTER TABLE game_events ADD CONSTRAINT FK_2EB2FA82E48FD905 FOREIGN KEY (game_id) REFERENCES games (id)
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
            ALTER TABLE goals ADD CONSTRAINT FK_C7241E2FE48FD905 FOREIGN KEY (game_id) REFERENCES games (id)
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
            ALTER TABLE player_game_stats ADD CONSTRAINT FK_3AD19A18E48FD905 FOREIGN KEY (game_id) REFERENCES games (id)
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
            ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE0E48FD905 FOREIGN KEY (game_id) REFERENCES games (id)
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
            ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F58AFC4DE FOREIGN KEY (league_id) REFERENCES league (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club ADD CONSTRAINT FK_690FCC09296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club ADD CONSTRAINT FK_690FCC0961190A32 FOREIGN KEY (club_id) REFERENCES clubs (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats ADD CONSTRAINT FK_5BAD6CE0E48FD905 FOREIGN KEY (game_id) REFERENCES games (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats ADD CONSTRAINT FK_5BAD6CE0296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD height DOUBLE PRECISION DEFAULT NULL, ADD weight DOUBLE PRECISION DEFAULT NULL, ADD shoe_size DOUBLE PRECISION DEFAULT NULL, ADD shirt_size VARCHAR(3) DEFAULT NULL, ADD pants_size VARCHAR(10) DEFAULT NULL, ADD new_email VARCHAR(180) DEFAULT NULL, ADD email_verification_token VARCHAR(100) DEFAULT NULL, ADD email_verification_token_expires_at DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E999E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E93C105691 FOREIGN KEY (coach_id) REFERENCES coach (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E961190A32 FOREIGN KEY (club_id) REFERENCES clubs (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE game_events DROP FOREIGN KEY FK_2EB2FA82E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY FK_C7241E2FE48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats DROP FOREIGN KEY FK_3AD19A18E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE0E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY FK_5BAD6CE0E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, home_team_id INT DEFAULT NULL, away_team_id INT DEFAULT NULL, game_type_id INT NOT NULL, location_id INT DEFAULT NULL, calendar_event_id INT DEFAULT NULL, date DATETIME DEFAULT NULL, home_score INT DEFAULT NULL, away_score INT DEFAULT NULL, is_finished TINYINT(1) NOT NULL, INDEX IDX_232B318C45185D02 (away_team_id), UNIQUE INDEX UNIQ_232B318C7495C8E3 (calendar_event_id), INDEX IDX_232B318C508EF3BC (game_type_id), INDEX IDX_232B318C64D218E (location_id), INDEX IDX_232B318C9C4C13F6 (home_team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY FK_FF232B319C4C13F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY FK_FF232B3145185D02
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY FK_FF232B31508EF3BC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY FK_FF232B3164D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP FOREIGN KEY FK_FF232B317495C8E3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_groups DROP FOREIGN KEY FK_C74AA47F7E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_group_members DROP FOREIGN KEY FK_8A0DC08BF7721D56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_group_members DROP FOREIGN KEY FK_8A0DC08BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_recipients DROP FOREIGN KEY FK_DBB61E5B537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message_recipients DROP FOREIGN KEY FK_DBB61E5BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE games
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_groups
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_group_members
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message_recipients
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_nationality_assignment DROP FOREIGN KEY FK_2CC6F51799E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_nationality_assignment DROP FOREIGN KEY FK_2CC6F5171C9DA55
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP FOREIGN KEY FK_264E43A63A74B23F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE players DROP FOREIGN KEY FK_264E43A6907E6D0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E999E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E961190A32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP height, DROP weight, DROP shoe_size, DROP shirt_size, DROP pants_size, DROP new_email, DROP email_verification_token, DROP email_verification_token_expires_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignment DROP FOREIGN KEY FK_F0EF85EB460F904B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_license_assignment DROP FOREIGN KEY FK_F0EF85EB3C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_alternative_positions DROP FOREIGN KEY FK_47210A0299E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_alternative_positions DROP FOREIGN KEY FK_47210A02DD842E46
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
            ALTER TABLE team_club DROP FOREIGN KEY FK_690FCC09296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_club DROP FOREIGN KEY FK_690FCC0961190A32
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
            ALTER TABLE coach_club_assignment DROP FOREIGN KEY FK_C61D1E553C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_club_assignment DROP FOREIGN KEY FK_C61D1E5561190A32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_game_stats DROP FOREIGN KEY FK_3AD19A1899E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FB09E220E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F58AFC4DE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignment DROP FOREIGN KEY FK_A019891C99E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE player_club_assignment DROP FOREIGN KEY FK_A019891C61190A32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE clubs DROP FOREIGN KEY FK_A5BD312364D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_nationality_assignment DROP FOREIGN KEY FK_16FBED593C105691
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE coach_nationality_assignment DROP FOREIGN KEY FK_16FBED591C9DA55
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY FK_5BAD6CE0296CD8AE
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
            ALTER TABLE goals DROP FOREIGN KEY FK_C7241E2F43B35028
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE goals DROP FOREIGN KEY FK_C7241E2FE9B9D4EE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBDAA1EEDA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F167F4F5D85
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F1664D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD type_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9E14F16C54C8C93 ON calendar_events (type_id)
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
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
