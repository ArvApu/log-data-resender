<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add resend_job table for background resend processing';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            CREATE TABLE resend_job (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                status VARCHAR(32) NOT NULL,
                source VARCHAR(255) NOT NULL,
                parser VARCHAR(255) NOT NULL,
                modifiers CLOB NOT NULL --(DC2Type:json)
                , filter CLOB DEFAULT NULL,
                filter_file_path VARCHAR(1024) DEFAULT NULL,
                counts CLOB NOT NULL --(DC2Type:json)
                , processed_count INTEGER NOT NULL,
                total_count INTEGER DEFAULT NULL,
                error_message CLOB DEFAULT NULL,
                created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
                , started_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
                , finished_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
                , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            )
        SQL;


        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE resend_job');
    }
}
