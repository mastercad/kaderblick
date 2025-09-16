<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909183129 extends AbstractMigration
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
            CREATE TABLE survey (
              id INT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              due_date DATETIME DEFAULT NULL,
              platform TINYINT(1) DEFAULT 0 NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_team (
              survey_id INT NOT NULL,
              team_id INT NOT NULL,
              INDEX idx_survey_team_survey_id (survey_id),
              INDEX idx_survey_team_team_id (team_id),
              PRIMARY KEY(survey_id, team_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_club (
              survey_id INT NOT NULL,
              club_id INT NOT NULL,
              INDEX idx_survey_club_survey_id (survey_id),
              INDEX idx_survey_club_club_id (club_id),
              PRIMARY KEY(survey_id, club_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_response (
              id INT AUTO_INCREMENT NOT NULL,
              survey_id INT NOT NULL,
              user_id INT NOT NULL,
              answers JSON NOT NULL COMMENT '(DC2Type:json)',
              created_at DATETIME NOT NULL,
              INDEX idx_survey_response_survey_id (survey_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_option (
              id INT AUTO_INCREMENT NOT NULL,
              option_text VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_option_survey_question (
              survey_option_id INT NOT NULL,
              survey_question_id INT NOT NULL,
              INDEX idx_survey_option_survey_question_survey_option_id (survey_option_id),
              INDEX idx_survey_option_survey_question_survey_question_id (survey_question_id),
              PRIMARY KEY(
                survey_option_id, survey_question_id
              )
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_option_type (
              id INT AUTO_INCREMENT NOT NULL,
              type_key VARCHAR(50) NOT NULL,
              name VARCHAR(100) NOT NULL,
              UNIQUE INDEX uniq_survey_option_type_type_key (type_key),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE survey_question (
              id INT AUTO_INCREMENT NOT NULL,
              survey_id INT NOT NULL,
              type_id INT NOT NULL,
              question_text VARCHAR(255) NOT NULL,
              INDEX idx_survey_question_survey_id (survey_id),
              INDEX idx_survey_question_type_id (type_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_team
            ADD
              CONSTRAINT fk_survey_team_survey_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_team
            ADD
              CONSTRAINT fk_survey_team_teams_team_id FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_club
            ADD
              CONSTRAINT fk_survey_club_survey_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_club
            ADD
              CONSTRAINT fk_survey_club_clubs_club_id FOREIGN KEY (club_id) REFERENCES clubs (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_response
            ADD
              CONSTRAINT fk_survey_response_survey_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_option_survey_question
            ADD
              CONSTRAINT fk_survey_option_survey_question_survey_option_survey_option FOREIGN KEY (survey_option_id) REFERENCES survey_option (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_option_survey_question
            ADD
              CONSTRAINT fk_survey_option_survey_question_survey_question_survey_question FOREIGN KEY (survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_question
            ADD
              CONSTRAINT fk_survey_question_survey_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_question
            ADD
              CONSTRAINT fk_survey_question_survey_option_type_type_id FOREIGN KEY (type_id) REFERENCES survey_option_type (id)
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
            ALTER TABLE survey_team DROP FOREIGN KEY fk_survey_team_survey_survey_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey_team DROP FOREIGN KEY fk_survey_team_teams_team_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey_club DROP FOREIGN KEY fk_survey_club_survey_survey_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey_club DROP FOREIGN KEY fk_survey_club_clubs_club_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey_response DROP FOREIGN KEY fk_survey_response_survey_survey_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_option_survey_question
            DROP
              FOREIGN KEY fk_survey_option_survey_question_survey_option_survey_option
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              survey_option_survey_question
            DROP
              FOREIGN KEY fk_survey_option_survey_question_survey_question_survey_question
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey_question DROP FOREIGN KEY fk_survey_question_survey_survey_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE survey_question DROP FOREIGN KEY fk_survey_question_survey_option_type_type_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_team
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_club
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_response
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_option
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_option_survey_question
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_option_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE survey_question
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
