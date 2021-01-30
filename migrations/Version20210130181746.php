<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210130181746 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE note (id UUID NOT NULL, account_id UUID NOT NULL, content TEXT NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFBDFA149B6B5FBA ON note (account_id)');
        $this->addSql('CREATE TABLE remote_server_send_data (id UUID NOT NULL, from_account_id UUID NOT NULL, to_account_id UUID NOT NULL, data JSONB NOT NULL, created_at INT NOT NULL, succeeded_at INT DEFAULT NULL, failed_count INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F1C8D477B0CF99BD ON remote_server_send_data (from_account_id)');
        $this->addSql('CREATE INDEX IDX_F1C8D477BC58BDC7 ON remote_server_send_data (to_account_id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA149B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE remote_server_send_data ADD CONSTRAINT FK_F1C8D477B0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE remote_server_send_data ADD CONSTRAINT FK_F1C8D477BC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE remote_server_send_data');
    }
}
