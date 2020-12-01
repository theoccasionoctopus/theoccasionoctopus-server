<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201201230756 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inbox_submission (id UUID NOT NULL, account_id UUID NOT NULL, data JSONB NOT NULL, ip TEXT NOT NULL, useragent TEXT NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_12733D189B6B5FBA ON inbox_submission (account_id)');
        $this->addSql('ALTER TABLE inbox_submission ADD CONSTRAINT FK_12733D189B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE inbox_submission');
    }
}
