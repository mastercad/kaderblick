<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250617214900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create event and event type tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_type (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            color VARCHAR(7) NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        $this->addSql('CREATE TABLE event (
            id INT AUTO_INCREMENT NOT NULL,
            type_id INT DEFAULT NULL,
            location_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME DEFAULT NULL,
            notification_sent TINYINT(1) NOT NULL DEFAULT 0,
            INDEX IDX_3BAE0AA7C54C8C93 (type_id),
            INDEX IDX_3BAE0AA764D218E (location_id),
            FOREIGN KEY (type_id) REFERENCES event_type (id),
            FOREIGN KEY (location_id) REFERENCES location (id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7C54C8C93');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E');
        $this->addSql('DROP TABLE event_type');
        $this->addSql('DROP TABLE event');
    }
}
