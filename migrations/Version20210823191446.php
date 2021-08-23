<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210823191446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD slug UUID NULL');
        $this->addSql('UPDATE event SET slug=id');
        $this->addSql('ALTER TABLE event ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX event_account_slug_idx ON event (account_id, slug)');

        $this->addSql('ALTER TABLE event_occurrence ADD slug UUID NULL');
        $this->addSql('UPDATE event_occurrence SET slug=id');
        $this->addSql('ALTER TABLE event_occurrence ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX event_occurrence_event_slug_idx ON event_occurrence (event_id, slug)');

        $this->addSql('ALTER TABLE history ADD slug UUID NULL');
        $this->addSql('UPDATE history SET slug=id');
        $this->addSql('ALTER TABLE history ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX history_account_slug_idx ON history (account_id, slug)');

        $this->addSql('ALTER TABLE import ADD slug UUID NULL');
        $this->addSql('UPDATE import SET slug=id');
        $this->addSql('ALTER TABLE import ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX import_account_slug_idx ON import (account_id, slug)');

        $this->addSql('ALTER TABLE tag ADD slug UUID NULL');
        $this->addSql('UPDATE tag SET slug=id');
        $this->addSql('ALTER TABLE tag ALTER COLUMN slug SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX tag_account_slug_idx ON tag (account_id, slug)');
    }

    public function down(Schema $schema): void
    {
        throw new \Exception('NA');
    }
}
