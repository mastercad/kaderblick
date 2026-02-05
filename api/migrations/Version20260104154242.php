<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104154242 extends AbstractMigration
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
            ALTER TABLE tournaments ADD created_by_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              tournaments
            ADD
              CONSTRAINT fk_tournaments_users_created_by_id FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_tournaments_created_by_id ON tournaments (created_by_id)
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
            ALTER TABLE tournaments DROP FOREIGN KEY fk_tournaments_users_created_by_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_tournaments_created_by_id ON tournaments
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tournaments DROP created_by_id
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
