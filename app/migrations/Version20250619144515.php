<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619144515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD player_id INT DEFAULT NULL, ADD coach_id INT DEFAULT NULL, ADD club_id INT DEFAULT NULL
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
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E999E6F5DF ON users (player_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E93C105691 ON users (coach_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1483A5E961190A32 ON users (club_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
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
            DROP INDEX UNIQ_1483A5E999E6F5DF ON users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1483A5E93C105691 ON users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1483A5E961190A32 ON users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP player_id, DROP coach_id, DROP club_id
        SQL);
    }
}
