<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718151752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE formation_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, background_path VARCHAR(255) NOT NULL, default_positions JSON DEFAULT NULL COMMENT '(DC2Type:json)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation DROP FOREIGN KEY FK_404021BF3C105691
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_404021BF3C105691 ON formation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD formation_type_id INT DEFAULT NULL, CHANGE coach_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD CONSTRAINT FK_404021BFA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD CONSTRAINT FK_404021BF80B3C9E8 FOREIGN KEY (formation_type_id) REFERENCES formation_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_404021BFA76ED395 ON formation (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_404021BF80B3C9E8 ON formation (formation_type_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE formation DROP FOREIGN KEY FK_404021BF80B3C9E8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE formation_type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation DROP FOREIGN KEY FK_404021BFA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_404021BFA76ED395 ON formation
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_404021BF80B3C9E8 ON formation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD coach_id INT DEFAULT NULL, DROP user_id, DROP formation_type_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD CONSTRAINT FK_404021BF3C105691 FOREIGN KEY (coach_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_404021BF3C105691 ON formation (coach_id)
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
