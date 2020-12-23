<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201223225723 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_follows_account ADD follow_requested BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE account_follows_account ALTER follows SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE inbox_submission ADD processed_at INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account_follows_account DROP follow_requested');
        $this->addSql('ALTER TABLE account_follows_account ALTER follows SET DEFAULT \'true\'');
        $this->addSql('ALTER TABLE inbox_submission DROP processed_at');
    }
}
