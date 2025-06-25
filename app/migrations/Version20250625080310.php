<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625080310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F167F4F5D85 FOREIGN KEY (calendar_event_type_id) REFERENCES calendar_event_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F1664D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F16C54C8C93 FOREIGN KEY (type_id) REFERENCES calendar_event_types (id)
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
            ALTER TABLE game DROP INDEX UNIQ_232B318C7495C8E3, ADD INDEX IDX_232B318C7495C8E3 (calendar_event_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game CHANGE calendar_event_id calendar_event_id INT NOT NULL
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
            ALTER TABLE game ADD CONSTRAINT FK_232B318C7495C8E3 FOREIGN KEY (calendar_event_id) REFERENCES calendar_events (id) ON DELETE CASCADE
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
            ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F58AFC4DE FOREIGN KEY (league_id) REFERENCES league (id)
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
            ALTER TABLE player_game_stats DROP FOREIGN KEY FK_3AD19A18E48FD905
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
            ALTER TABLE team_game_stats DROP FOREIGN KEY FK_5BAD6CE0E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_game_stats DROP FOREIGN KEY FK_5BAD6CE0296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game DROP INDEX IDX_232B318C7495C8E3, ADD UNIQUE INDEX UNIQ_232B318C7495C8E3 (calendar_event_id)
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
            ALTER TABLE game DROP FOREIGN KEY FK_232B318C7495C8E3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game CHANGE calendar_event_id calendar_event_id INT DEFAULT NULL
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
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F167F4F5D85
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F1664D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F16C54C8C93
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
