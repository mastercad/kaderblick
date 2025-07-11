<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds user relations and modifies user associations';
    }

    public function up(Schema $schema): void
    {
        // Erst Foreign Keys entfernen
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E999E6F5DF');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93C105691');

        // Dann die Unique Indices entfernen
        $this->addSql('ALTER TABLE users DROP INDEX UNIQ_1483A5E999E6F5DF');
        $this->addSql('ALTER TABLE users DROP INDEX UNIQ_1483A5E93C105691');

        // Foreign Keys wieder hinzufügen, aber ohne UNIQUE constraint
        $this->addSql('ALTER TABLE users 
            ADD CONSTRAINT FK_1483A5E999E6F5DF 
            FOREIGN KEY (player_id) 
            REFERENCES players (id)');

        $this->addSql('ALTER TABLE users 
            ADD CONSTRAINT FK_1483A5E93C105691 
            FOREIGN KEY (coach_id) 
            REFERENCES coach (id)');

        // Neue Tabelle für User Relations
        $this->addSql('CREATE TABLE user_relations (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            related_user_id INT NOT NULL,
            player_id INT DEFAULT NULL,
            coach_id INT DEFAULT NULL,
            relation_type VARCHAR(50) NOT NULL,
            permissions JSON NOT NULL,
            INDEX IDX_user (user_id),
            INDEX IDX_related_user (related_user_id),
            INDEX IDX_player (player_id),
            INDEX IDX_coach (coach_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE user_relations 
            ADD CONSTRAINT FK_user_relations_user 
            FOREIGN KEY (user_id) 
            REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE user_relations 
            ADD CONSTRAINT FK_user_relations_related_user 
            FOREIGN KEY (related_user_id) 
            REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE user_relations 
            ADD CONSTRAINT FK_user_relations_player 
            FOREIGN KEY (player_id) 
            REFERENCES players (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE user_relations 
            ADD CONSTRAINT FK_user_relations_coach 
            FOREIGN KEY (coach_id) 
            REFERENCES coach (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Erst Foreign Keys der user_relations Tabelle entfernen
        $this->addSql('DROP TABLE user_relations');

        // Foreign Keys der users Tabelle entfernen
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E999E6F5DF');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93C105691');

        // Unique Indices wieder hinzufügen
        $this->addSql('ALTER TABLE users 
            ADD UNIQUE INDEX UNIQ_1483A5E999E6F5DF (player_id)');
        $this->addSql('ALTER TABLE users 
            ADD UNIQUE INDEX UNIQ_1483A5E93C105691 (coach_id)');

        // Foreign Keys mit UNIQUE constraint wieder hinzufügen
        $this->addSql('ALTER TABLE users 
            ADD CONSTRAINT FK_1483A5E999E6F5DF 
            FOREIGN KEY (player_id) 
            REFERENCES players (id)');
        $this->addSql('ALTER TABLE users 
            ADD CONSTRAINT FK_1483A5E93C105691 
            FOREIGN KEY (coach_id) 
            REFERENCES coach (id)');
    }
}
