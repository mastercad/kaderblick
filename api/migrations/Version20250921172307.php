<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921172307 extends AbstractMigration
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
            DROP INDEX uniq_push_endpoint ON push_subscriptions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_push_endpoint ON push_subscriptions (user_id, endpoint)
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
            DROP INDEX uniq_push_endpoint ON push_subscriptions
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_push_endpoint ON push_subscriptions (endpoint)
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
