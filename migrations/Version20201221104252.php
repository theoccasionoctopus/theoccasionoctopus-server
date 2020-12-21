<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201221104252 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account_remote ADD username VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE account_remote ADD web_finger_data JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE account_remote ADD web_finger_data_last_fetched INT DEFAULT NULL');
        $this->addSql('ALTER TABLE account_remote ADD actor_data JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE account_remote ADD actor_data_last_fetched INT DEFAULT NULL');
        $this->addSql('ALTER TABLE account_remote ADD actor_data_id VARCHAR(2000) DEFAULT NULL');
        $this->addSql('ALTER TABLE account_remote DROP human_url');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D82B1C11500F156 ON account_remote (actor_data_id)');
        $this->addSql('ALTER TABLE event ADD activitypub_id VARCHAR(2000) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA717C73A58 ON event (activitypub_id)');
        $this->addSql('ALTER TABLE remote_server ADD occasion_octopus_software BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_7D82B1C11500F156');
        $this->addSql('ALTER TABLE account_remote ADD human_url TEXT NOT NULL');
        $this->addSql('ALTER TABLE account_remote DROP username');
        $this->addSql('ALTER TABLE account_remote DROP web_finger_data');
        $this->addSql('ALTER TABLE account_remote DROP web_finger_data_last_fetched');
        $this->addSql('ALTER TABLE account_remote DROP actor_data');
        $this->addSql('ALTER TABLE account_remote DROP actor_data_last_fetched');
        $this->addSql('ALTER TABLE account_remote DROP actor_data_id');
        $this->addSql('ALTER TABLE remote_server DROP occasion_octopus_software');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA717C73A58');
        $this->addSql('ALTER TABLE event DROP activitypub_id');
    }
}
