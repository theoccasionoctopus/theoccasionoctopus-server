<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201030161530 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE api_access_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE country_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE remote_server_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE timezone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE Country_has_timezone (country_id INT NOT NULL, timezone_id INT NOT NULL, PRIMARY KEY(country_id, timezone_id))');
        $this->addSql('CREATE INDEX IDX_303894BAF92F3E70 ON Country_has_timezone (country_id)');
        $this->addSql('CREATE INDEX IDX_303894BA3FE997DE ON Country_has_timezone (timezone_id)');
        $this->addSql('CREATE TABLE account (id UUID NOT NULL, title VARCHAR(500) NOT NULL, years_behind SMALLINT DEFAULT 10 NOT NULL, years_ahead SMALLINT DEFAULT 10 NOT NULL, created_at INT NOT NULL, limit_number_of_events INT DEFAULT 100000 NOT NULL, limit_number_of_event_occurrences INT DEFAULT 100000 NOT NULL, limit_number_of_tags INT DEFAULT 100000 NOT NULL, limit_number_of_accounts_following INT DEFAULT 10000 NOT NULL, limit_number_of_imports INT DEFAULT 100 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE account_follows_account (account_id UUID NOT NULL, follows_account_id UUID NOT NULL, follows BOOLEAN DEFAULT \'true\' NOT NULL, PRIMARY KEY(account_id, follows_account_id))');
        $this->addSql('CREATE INDEX IDX_46039E609B6B5FBA ON account_follows_account (account_id)');
        $this->addSql('CREATE INDEX IDX_46039E607FDE6122 ON account_follows_account (follows_account_id)');
        $this->addSql('CREATE TABLE account_local (account_id UUID NOT NULL, default_country_id INT DEFAULT NULL, default_timezone_id INT DEFAULT NULL, username VARCHAR(500) NOT NULL, username_canonical VARCHAR(500) NOT NULL, default_privacy SMALLINT DEFAULT 10000 NOT NULL, PRIMARY KEY(account_id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD59519992FC23A8 ON account_local (username_canonical)');
        $this->addSql('CREATE INDEX IDX_BD595199B10C6A13 ON account_local (default_country_id)');
        $this->addSql('CREATE INDEX IDX_BD595199EB1A8468 ON account_local (default_timezone_id)');
        $this->addSql('CREATE TABLE account_remote (account_id UUID NOT NULL, remote_server_id INT NOT NULL, human_url TEXT NOT NULL, PRIMARY KEY(account_id))');
        $this->addSql('CREATE INDEX IDX_7D82B1C167FD7973 ON account_remote (remote_server_id)');
        $this->addSql('CREATE TABLE api_access_token (id INT NOT NULL, user_id INT NOT NULL, account_id UUID DEFAULT NULL, token VARCHAR(200) NOT NULL, enabled BOOLEAN DEFAULT \'true\' NOT NULL, write BOOLEAN DEFAULT \'false\' NOT NULL, note TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BCC804C55F37A13B ON api_access_token (token)');
        $this->addSql('CREATE INDEX IDX_BCC804C5A76ED395 ON api_access_token (user_id)');
        $this->addSql('CREATE INDEX IDX_BCC804C59B6B5FBA ON api_access_token (account_id)');
        $this->addSql('CREATE TABLE country (id INT NOT NULL, title VARCHAR(500) DEFAULT NULL, iso3166_two_char VARCHAR(2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5373C966178159D2 ON country (iso3166_two_char)');
        $this->addSql('CREATE TABLE email_user_upcoming_events_for_account (user_id INT NOT NULL, account_id UUID NOT NULL, enabled BOOLEAN DEFAULT \'true\' NOT NULL, token VARCHAR(100) NOT NULL, PRIMARY KEY(user_id, account_id))');
        $this->addSql('CREATE INDEX IDX_FDEBD251A76ED395 ON email_user_upcoming_events_for_account (user_id)');
        $this->addSql('CREATE INDEX IDX_FDEBD2519B6B5FBA ON email_user_upcoming_events_for_account (account_id)');
        $this->addSql('CREATE TABLE event (id UUID NOT NULL, account_id UUID NOT NULL, country_id INT DEFAULT NULL, timezone_id INT DEFAULT NULL, title TEXT DEFAULT NULL, description TEXT DEFAULT NULL, extra_fields JSONB DEFAULT NULL, start_year SMALLINT NOT NULL, start_month SMALLINT NOT NULL, start_day SMALLINT NOT NULL, start_hour SMALLINT NOT NULL, start_minute SMALLINT NOT NULL, start_second SMALLINT NOT NULL, end_year SMALLINT NOT NULL, end_month SMALLINT NOT NULL, end_day SMALLINT NOT NULL, end_hour SMALLINT NOT NULL, end_minute SMALLINT NOT NULL, end_second SMALLINT NOT NULL, cached_start_epoch INT NOT NULL, cached_end_epoch INT NOT NULL, deleted BOOLEAN NOT NULL, cancelled BOOLEAN NOT NULL, privacy SMALLINT DEFAULT 10000 NOT NULL, url TEXT DEFAULT NULL, url_tickets TEXT DEFAULT NULL, rrule TEXT DEFAULT NULL, rrule_options JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA79B6B5FBA ON event (account_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7F92F3E70 ON event (country_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA73FE997DE ON event (timezone_id)');
        $this->addSql('CREATE TABLE event_has_import (event_id UUID NOT NULL, import_id UUID NOT NULL, primary_id_in_data TEXT NOT NULL, secondary_id_in_data TEXT NOT NULL, PRIMARY KEY(event_id, import_id))');
        $this->addSql('CREATE INDEX IDX_E98200A971F7E88B ON event_has_import (event_id)');
        $this->addSql('CREATE INDEX IDX_E98200A9B6A263D9 ON event_has_import (import_id)');
        $this->addSql('CREATE UNIQUE INDEX event_has_import_import_event_id_in_data_idx ON event_has_import (import_id, primary_id_in_data, secondary_id_in_data)');
        $this->addSql('CREATE TABLE event_has_source_event (event_id UUID NOT NULL, source_event_id UUID NOT NULL, PRIMARY KEY(event_id, source_event_id))');
        $this->addSql('CREATE INDEX IDX_1ABB477371F7E88B ON event_has_source_event (event_id)');
        $this->addSql('CREATE INDEX IDX_1ABB477332D7DE59 ON event_has_source_event (source_event_id)');
        $this->addSql('CREATE TABLE event_has_tag (event_id UUID NOT NULL, tag_id UUID NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(event_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_B21E43D671F7E88B ON event_has_tag (event_id)');
        $this->addSql('CREATE INDEX IDX_B21E43D6BAD26311 ON event_has_tag (tag_id)');
        $this->addSql('CREATE TABLE event_occurrence (id UUID NOT NULL, event_id UUID NOT NULL, start_epoch INT NOT NULL, end_epoch INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E61358DC71F7E88B ON event_occurrence (event_id)');
        $this->addSql('CREATE UNIQUE INDEX event_occurrence_event_start_idx ON event_occurrence (event_id, start_epoch)');
        $this->addSql('CREATE TABLE history (id UUID NOT NULL, account_id UUID NOT NULL, creator_id INT DEFAULT NULL, created_at INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27BA704B9B6B5FBA ON history (account_id)');
        $this->addSql('CREATE INDEX IDX_27BA704B61220EA6 ON history (creator_id)');
        $this->addSql('CREATE TABLE history_has_event (history_id UUID NOT NULL, event_id UUID NOT NULL, PRIMARY KEY(history_id, event_id))');
        $this->addSql('CREATE INDEX IDX_566E49FB1E058452 ON history_has_event (history_id)');
        $this->addSql('CREATE INDEX IDX_566E49FB71F7E88B ON history_has_event (event_id)');
        $this->addSql('CREATE TABLE history_has_event_has_tag (history_id UUID NOT NULL, event_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(history_id, event_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_B53E67F31E058452 ON history_has_event_has_tag (history_id)');
        $this->addSql('CREATE INDEX IDX_B53E67F371F7E88B ON history_has_event_has_tag (event_id)');
        $this->addSql('CREATE INDEX IDX_B53E67F3BAD26311 ON history_has_event_has_tag (tag_id)');
        $this->addSql('CREATE TABLE history_has_tag (history_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(history_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_132EC6931E058452 ON history_has_tag (history_id)');
        $this->addSql('CREATE INDEX IDX_132EC693BAD26311 ON history_has_tag (tag_id)');
        $this->addSql('CREATE TABLE import (id UUID NOT NULL, account_id UUID NOT NULL, default_country_id INT DEFAULT NULL, default_timezone_id INT DEFAULT NULL, url TEXT NOT NULL, enabled BOOLEAN DEFAULT \'true\' NOT NULL, privacy SMALLINT DEFAULT 0 NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9D4ECE1D9B6B5FBA ON import (account_id)');
        $this->addSql('CREATE INDEX IDX_9D4ECE1DB10C6A13 ON import (default_country_id)');
        $this->addSql('CREATE INDEX IDX_9D4ECE1DEB1A8468 ON import (default_timezone_id)');
        $this->addSql('CREATE UNIQUE INDEX ical_import_account_url_idx ON import (account_id, url)');
        $this->addSql('CREATE TABLE remote_server (id INT NOT NULL, host VARCHAR(500) NOT NULL, ssl BOOLEAN NOT NULL, title VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tag (id UUID NOT NULL, account_id UUID NOT NULL, title TEXT NOT NULL, description TEXT DEFAULT NULL, enabled BOOLEAN NOT NULL, privacy SMALLINT DEFAULT 10000 NOT NULL, extra_fields JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_389B7839B6B5FBA ON tag (account_id)');
        $this->addSql('CREATE UNIQUE INDEX tag_account_title_idx ON tag (account_id, title)');
        $this->addSql('CREATE TABLE timezone (id INT NOT NULL, title VARCHAR(500) DEFAULT NULL, code VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3701B29777153098 ON timezone (code)');
        $this->addSql('CREATE TABLE user_account (id INT NOT NULL, email VARCHAR(500) NOT NULL, email_canonical VARCHAR(500) NOT NULL, title VARCHAR(500) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at INT NOT NULL, locked BOOLEAN DEFAULT \'false\' NOT NULL, limit_number_of_accounts_manage INT DEFAULT 100 NOT NULL, limit_number_of_api_access_tokens INT DEFAULT 100 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B48AEA0D96FBF ON user_account (email_canonical)');
        $this->addSql('CREATE TABLE user_manage_account (user_id INT NOT NULL, account_id UUID NOT NULL, PRIMARY KEY(user_id, account_id))');
        $this->addSql('CREATE INDEX IDX_4BD61F8BA76ED395 ON user_manage_account (user_id)');
        $this->addSql('CREATE INDEX IDX_4BD61F8B9B6B5FBA ON user_manage_account (account_id)');
        $this->addSql('ALTER TABLE Country_has_timezone ADD CONSTRAINT FK_303894BAF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE Country_has_timezone ADD CONSTRAINT FK_303894BA3FE997DE FOREIGN KEY (timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_follows_account ADD CONSTRAINT FK_46039E609B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_follows_account ADD CONSTRAINT FK_46039E607FDE6122 FOREIGN KEY (follows_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_local ADD CONSTRAINT FK_BD5951999B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_local ADD CONSTRAINT FK_BD595199B10C6A13 FOREIGN KEY (default_country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_local ADD CONSTRAINT FK_BD595199EB1A8468 FOREIGN KEY (default_timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_remote ADD CONSTRAINT FK_7D82B1C19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_remote ADD CONSTRAINT FK_7D82B1C167FD7973 FOREIGN KEY (remote_server_id) REFERENCES remote_server (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE api_access_token ADD CONSTRAINT FK_BCC804C5A76ED395 FOREIGN KEY (user_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE api_access_token ADD CONSTRAINT FK_BCC804C59B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_user_upcoming_events_for_account ADD CONSTRAINT FK_FDEBD251A76ED395 FOREIGN KEY (user_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_user_upcoming_events_for_account ADD CONSTRAINT FK_FDEBD2519B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA79B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73FE997DE FOREIGN KEY (timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_import ADD CONSTRAINT FK_E98200A971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_import ADD CONSTRAINT FK_E98200A9B6A263D9 FOREIGN KEY (import_id) REFERENCES import (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_source_event ADD CONSTRAINT FK_1ABB477371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_source_event ADD CONSTRAINT FK_1ABB477332D7DE59 FOREIGN KEY (source_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_tag ADD CONSTRAINT FK_B21E43D671F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_has_tag ADD CONSTRAINT FK_B21E43D6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_occurrence ADD CONSTRAINT FK_E61358DC71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B61220EA6 FOREIGN KEY (creator_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event ADD CONSTRAINT FK_566E49FB1E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event ADD CONSTRAINT FK_566E49FB71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event_has_tag ADD CONSTRAINT FK_B53E67F31E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event_has_tag ADD CONSTRAINT FK_B53E67F371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_event_has_tag ADD CONSTRAINT FK_B53E67F3BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_tag ADD CONSTRAINT FK_132EC6931E058452 FOREIGN KEY (history_id) REFERENCES history (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history_has_tag ADD CONSTRAINT FK_132EC693BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DB10C6A13 FOREIGN KEY (default_country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DEB1A8468 FOREIGN KEY (default_timezone_id) REFERENCES timezone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B7839B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_manage_account ADD CONSTRAINT FK_4BD61F8BA76ED395 FOREIGN KEY (user_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_manage_account ADD CONSTRAINT FK_4BD61F8B9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account_follows_account DROP CONSTRAINT FK_46039E609B6B5FBA');
        $this->addSql('ALTER TABLE account_follows_account DROP CONSTRAINT FK_46039E607FDE6122');
        $this->addSql('ALTER TABLE account_local DROP CONSTRAINT FK_BD5951999B6B5FBA');
        $this->addSql('ALTER TABLE account_remote DROP CONSTRAINT FK_7D82B1C19B6B5FBA');
        $this->addSql('ALTER TABLE api_access_token DROP CONSTRAINT FK_BCC804C59B6B5FBA');
        $this->addSql('ALTER TABLE email_user_upcoming_events_for_account DROP CONSTRAINT FK_FDEBD2519B6B5FBA');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA79B6B5FBA');
        $this->addSql('ALTER TABLE history DROP CONSTRAINT FK_27BA704B9B6B5FBA');
        $this->addSql('ALTER TABLE import DROP CONSTRAINT FK_9D4ECE1D9B6B5FBA');
        $this->addSql('ALTER TABLE tag DROP CONSTRAINT FK_389B7839B6B5FBA');
        $this->addSql('ALTER TABLE user_manage_account DROP CONSTRAINT FK_4BD61F8B9B6B5FBA');
        $this->addSql('ALTER TABLE Country_has_timezone DROP CONSTRAINT FK_303894BAF92F3E70');
        $this->addSql('ALTER TABLE account_local DROP CONSTRAINT FK_BD595199B10C6A13');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7F92F3E70');
        $this->addSql('ALTER TABLE import DROP CONSTRAINT FK_9D4ECE1DB10C6A13');
        $this->addSql('ALTER TABLE event_has_import DROP CONSTRAINT FK_E98200A971F7E88B');
        $this->addSql('ALTER TABLE event_has_source_event DROP CONSTRAINT FK_1ABB477371F7E88B');
        $this->addSql('ALTER TABLE event_has_source_event DROP CONSTRAINT FK_1ABB477332D7DE59');
        $this->addSql('ALTER TABLE event_has_tag DROP CONSTRAINT FK_B21E43D671F7E88B');
        $this->addSql('ALTER TABLE event_occurrence DROP CONSTRAINT FK_E61358DC71F7E88B');
        $this->addSql('ALTER TABLE history_has_event DROP CONSTRAINT FK_566E49FB71F7E88B');
        $this->addSql('ALTER TABLE history_has_event_has_tag DROP CONSTRAINT FK_B53E67F371F7E88B');
        $this->addSql('ALTER TABLE history_has_event DROP CONSTRAINT FK_566E49FB1E058452');
        $this->addSql('ALTER TABLE history_has_event_has_tag DROP CONSTRAINT FK_B53E67F31E058452');
        $this->addSql('ALTER TABLE history_has_tag DROP CONSTRAINT FK_132EC6931E058452');
        $this->addSql('ALTER TABLE event_has_import DROP CONSTRAINT FK_E98200A9B6A263D9');
        $this->addSql('ALTER TABLE account_remote DROP CONSTRAINT FK_7D82B1C167FD7973');
        $this->addSql('ALTER TABLE event_has_tag DROP CONSTRAINT FK_B21E43D6BAD26311');
        $this->addSql('ALTER TABLE history_has_event_has_tag DROP CONSTRAINT FK_B53E67F3BAD26311');
        $this->addSql('ALTER TABLE history_has_tag DROP CONSTRAINT FK_132EC693BAD26311');
        $this->addSql('ALTER TABLE Country_has_timezone DROP CONSTRAINT FK_303894BA3FE997DE');
        $this->addSql('ALTER TABLE account_local DROP CONSTRAINT FK_BD595199EB1A8468');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA73FE997DE');
        $this->addSql('ALTER TABLE import DROP CONSTRAINT FK_9D4ECE1DEB1A8468');
        $this->addSql('ALTER TABLE api_access_token DROP CONSTRAINT FK_BCC804C5A76ED395');
        $this->addSql('ALTER TABLE email_user_upcoming_events_for_account DROP CONSTRAINT FK_FDEBD251A76ED395');
        $this->addSql('ALTER TABLE history DROP CONSTRAINT FK_27BA704B61220EA6');
        $this->addSql('ALTER TABLE user_manage_account DROP CONSTRAINT FK_4BD61F8BA76ED395');
        $this->addSql('DROP SEQUENCE api_access_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE country_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE remote_server_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE timezone_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_account_id_seq CASCADE');
        $this->addSql('DROP TABLE Country_has_timezone');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE account_follows_account');
        $this->addSql('DROP TABLE account_local');
        $this->addSql('DROP TABLE account_remote');
        $this->addSql('DROP TABLE api_access_token');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE email_user_upcoming_events_for_account');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_has_import');
        $this->addSql('DROP TABLE event_has_source_event');
        $this->addSql('DROP TABLE event_has_tag');
        $this->addSql('DROP TABLE event_occurrence');
        $this->addSql('DROP TABLE history');
        $this->addSql('DROP TABLE history_has_event');
        $this->addSql('DROP TABLE history_has_event_has_tag');
        $this->addSql('DROP TABLE history_has_tag');
        $this->addSql('DROP TABLE import');
        $this->addSql('DROP TABLE remote_server');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE timezone');
        $this->addSql('DROP TABLE user_account');
        $this->addSql('DROP TABLE user_manage_account');
    }
}
