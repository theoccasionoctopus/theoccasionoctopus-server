<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210226203822 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ALTER start_hour DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER start_minute DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER start_second DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER end_hour DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER end_minute DROP NOT NULL');
        $this->addSql('ALTER TABLE event ALTER end_second DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event ALTER start_hour SET NOT NULL');
        $this->addSql('ALTER TABLE event ALTER start_minute SET NOT NULL');
        $this->addSql('ALTER TABLE event ALTER start_second SET NOT NULL');
        $this->addSql('ALTER TABLE event ALTER end_hour SET NOT NULL');
        $this->addSql('ALTER TABLE event ALTER end_minute SET NOT NULL');
        $this->addSql('ALTER TABLE event ALTER end_second SET NOT NULL');
    }
}
