<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230703105709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add log tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, message VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE TABLE sender_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, log_id INTEGER NOT NULL, model_id VARCHAR(255) NOT NULL, failed_at INTEGER NOT NULL, master_user_id VARCHAR(255) DEFAULT NULL, session VARCHAR(255) NOT NULL, response_data CLOB DEFAULT NULL --(DC2Type:json)
        , CONSTRAINT FK_6AA3CB1CEA675D86 FOREIGN KEY (log_id) REFERENCES log (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6AA3CB1CEA675D86 ON sender_log (log_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE sender_log');
    }
}
