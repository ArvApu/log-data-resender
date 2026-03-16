<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260315090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add log, sender_log, and resend_job tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE log (
                id SERIAL PRIMARY KEY NOT NULL,
                message VARCHAR(255) NOT NULL,
                level VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL
            )
        ');

        $this->addSql('
            CREATE TABLE sender_log (
                id SERIAL PRIMARY KEY NOT NULL,
                log_id INTEGER NOT NULL,
                model_id VARCHAR(255) NOT NULL,
                failed_at INTEGER NOT NULL,
                master_user_id VARCHAR(255) DEFAULT NULL,
                session VARCHAR(255) NOT NULL,
                response_data TEXT DEFAULT NULL,
                CONSTRAINT FK_6AA3CB1CEA675D86 FOREIGN KEY (log_id) REFERENCES log (id)
            )
        ');

        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_6AA3CB1CEA675D86 ON sender_log (log_id)
        ');

        $this->addSql('
            CREATE TABLE resend_job (
                id SERIAL PRIMARY KEY NOT NULL,
                status VARCHAR(32) NOT NULL,
                source VARCHAR(255) NOT NULL,
                parser VARCHAR(255) NOT NULL,
                modifiers TEXT NOT NULL,
                filter TEXT DEFAULT NULL,
                filter_file_path VARCHAR(1024) DEFAULT NULL,
                counts TEXT NOT NULL,
                processed_count INTEGER NOT NULL,
                total_count INTEGER DEFAULT NULL,
                error_message TEXT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL,
                started_at TIMESTAMP DEFAULT NULL,
                finished_at TIMESTAMP DEFAULT NULL,
                updated_at TIMESTAMP NOT NULL
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE resend_job');
        $this->addSql('DROP TABLE sender_log');
        $this->addSql('DROP TABLE log');
    }
}
