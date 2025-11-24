<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124121314 extends AbstractMigration
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
            CREATE TABLE video_segments (id INT AUTO_INCREMENT NOT NULL, video_id INT NOT NULL, user_id INT NOT NULL, start_minute DOUBLE PRECISION NOT NULL, length_seconds INT NOT NULL, title VARCHAR(255) DEFAULT NULL, sub_title VARCHAR(255) DEFAULT NULL, include_audio TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', sort_order INT DEFAULT 0 NOT NULL, INDEX idx_video_segments_video_id (video_id), INDEX idx_video_segments_user_id (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_segments ADD CONSTRAINT fk_video_segments_videos_video_id FOREIGN KEY (video_id) REFERENCES videos (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_segments ADD CONSTRAINT fk_video_segments_users_user_id FOREIGN KEY (user_id) REFERENCES users (id)
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
            ALTER TABLE video_segments DROP FOREIGN KEY fk_video_segments_videos_video_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_segments DROP FOREIGN KEY fk_video_segments_users_user_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE video_segments
        SQL);
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
