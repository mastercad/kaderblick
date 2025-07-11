<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709175000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates user_relations table to use relation_types';
    }

    public function up(Schema $schema): void
    {
        // Temporäre Spalte für die Migration
        $this->addSql('ALTER TABLE user_relations ADD relation_type_id INT DEFAULT NULL AFTER coach_id');
        $this->addSql('ALTER TABLE user_relations ADD CONSTRAINT FK_user_relations_type FOREIGN KEY (relation_type_id) REFERENCES relation_types (id)');

        // Migriere existierende Daten
        $this->addSql('UPDATE user_relations ur 
            INNER JOIN relation_types rt ON rt.identifier = ur.relation_type 
            SET ur.relation_type_id = rt.id');

        // Mache die neue Spalte NOT NULL und entferne die alte
        $this->addSql('ALTER TABLE user_relations MODIFY relation_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_relations DROP relation_type');
    }

    public function down(Schema $schema): void
    {
        // Alte Spalte wieder hinzufügen
        $this->addSql('ALTER TABLE user_relations ADD relation_type VARCHAR(50) DEFAULT NULL AFTER coach_id');

        // Migriere Daten zurück
        $this->addSql('UPDATE user_relations ur 
            INNER JOIN relation_types rt ON rt.id = ur.relation_type_id 
            SET ur.relation_type = rt.identifier');

        // Mache die alte Spalte NOT NULL und entferne die neue
        $this->addSql('ALTER TABLE user_relations MODIFY relation_type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE user_relations DROP FOREIGN KEY FK_user_relations_type');
        $this->addSql('ALTER TABLE user_relations DROP relation_type_id');
    }
}
