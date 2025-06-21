<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619183349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE calendar_event_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE calendar_events (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, calendar_event_type_id INT DEFAULT NULL, location_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, notification_sent TINYINT(1) NOT NULL, INDEX IDX_F9E14F16C54C8C93 (type_id), INDEX IDX_F9E14F167F4F5D85 (calendar_event_type_id), INDEX IDX_F9E14F1664D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participations (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, event_id INT NOT NULL, is_participating TINYINT(1) NOT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_FDC6C6E899E6F5DF (player_id), INDEX IDX_FDC6C6E871F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F16C54C8C93 FOREIGN KEY (type_id) REFERENCES calendar_event_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F167F4F5D85 FOREIGN KEY (calendar_event_type_id) REFERENCES calendar_event_types (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events ADD CONSTRAINT FK_F9E14F1664D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations ADD CONSTRAINT FK_FDC6C6E899E6F5DF FOREIGN KEY (player_id) REFERENCES players (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations ADD CONSTRAINT FK_FDC6C6E871F7E88B FOREIGN KEY (event_id) REFERENCES calendar_events (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7C54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE event_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, color VARCHAR(7) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, location_id INT DEFAULT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, notification_sent TINYINT(1) NOT NULL, INDEX IDX_3BAE0AA7C54C8C93 (type_id), INDEX IDX_3BAE0AA764D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C54C8C93 FOREIGN KEY (type_id) REFERENCES event_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F16C54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F167F4F5D85
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE calendar_events DROP FOREIGN KEY FK_F9E14F1664D218E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations DROP FOREIGN KEY FK_FDC6C6E899E6F5DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participations DROP FOREIGN KEY FK_FDC6C6E871F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar_event_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE calendar_events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participations
        SQL);
    }
}
