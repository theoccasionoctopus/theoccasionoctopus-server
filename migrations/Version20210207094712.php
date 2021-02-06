<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207094712 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE history_has_event_has_source_event (history_id UUID NOT NULL, event_id UUID NOT NULL, source_event_id UUID NOT NULL, PRIMARY KEY(history_id, event_id, source_event_id))');
        $this->addSql('CREATE INDEX IDX_7D59E5171E058452 ON history_has_event_has_source_event (history_id)');
        $this->addSql('CREATE INDEX IDX_7D59E51771F7E88B ON history_has_event_has_source_event (event_id)');
        $this->addSql('CREATE INDEX IDX_7D59E51732D7DE59 ON history_has_event_has_source_event (source_event_id)');
        $this->addSql('ALTER TABLE history_has_event_has_source_event ADD CONSTRAINT FK_7D59E5171E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event_has_source_event ADD CONSTRAINT FK_7D59E51771F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event_has_source_event ADD CONSTRAINT FK_7D59E51732D7DE59 FOREIGN KEY (source_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_source_event ADD update_all BOOLEAN DEFAULT \'true\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE history_has_event_has_source_event');
        $this->addSql('ALTER TABLE event_has_source_event DROP update_all');
    }
}
