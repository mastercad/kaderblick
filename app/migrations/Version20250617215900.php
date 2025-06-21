<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250617215900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add latitude and longitude to location table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE location ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE location DROP latitude, DROP longitude');
    }
}
