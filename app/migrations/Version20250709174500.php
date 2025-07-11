<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709174500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates relation types and permissions tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE relation_types (
            id INT AUTO_INCREMENT NOT NULL,
            identifier VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            category VARCHAR(20) NOT NULL,
            UNIQUE INDEX UNIQ_relation_type_identifier (identifier),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE permissions (
            id INT AUTO_INCREMENT NOT NULL,
            identifier VARCHAR(50) NOT NULL,
            name VARCHAR(100) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX UNIQ_permission_identifier (identifier),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Standard-Beziehungstypen einfügen
        $this->addSql("INSERT INTO relation_types (identifier, name, category) VALUES
            ('parent', 'Elternteil', 'player'),
            ('sibling', 'Geschwister', 'player'),
            ('relative', 'Verwandter', 'player'),
            ('guardian', 'Erziehungsberechtigter', 'player'),
            ('friend', 'Freund', 'player'),
            ('assistant', 'Assistent', 'coach'),
            ('observer', 'Beobachter', 'coach'),
            ('substitute', 'Vertretung', 'coach'),
            ('mentor', 'Mentor', 'coach')");

        // Standard-Berechtigungen einfügen
        $this->addSql("INSERT INTO permissions (identifier, name, description) VALUES
            ('view_profile', 'Profil ansehen', 'Erlaubt das Ansehen des kompletten Profils'),
            ('view_medical', 'Medizinische Daten ansehen', 'Erlaubt Zugriff auf medizinische Informationen'),
            ('view_stats', 'Statistiken ansehen', 'Erlaubt das Ansehen von Leistungsstatistiken'),
            ('manage_attendance', 'Anwesenheit verwalten', 'Erlaubt An-/Abmeldungen zu Terminen'),
            ('view_documents', 'Dokumente ansehen', 'Erlaubt Zugriff auf hochgeladene Dokumente')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE relation_types');
        $this->addSql('DROP TABLE permissions');
    }
}
