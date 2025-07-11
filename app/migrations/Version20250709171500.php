<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250709171500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates dashboard_widget table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dashboard_widget (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            config JSON NOT NULL,
            position INT NOT NULL,
            width INT NOT NULL,
            enabled TINYINT(1) NOT NULL,
            INDEX IDX_dashboard_widget_user (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE dashboard_widget 
            ADD CONSTRAINT FK_dashboard_widget_user 
            FOREIGN KEY (user_id) 
            REFERENCES users (id)
            ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dashboard_widget');
    }
}
