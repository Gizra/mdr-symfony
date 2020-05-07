<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200304183747 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE group_meeting_attendance (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, person_id CHAR(36) NOT NULL --(DC2Type:guid)
        , group_meeting_id CHAR(36) NOT NULL --(DC2Type:guid)
        )');
        $this->addSql('CREATE UNIQUE INDEX person_group_meeting_unique ON group_meeting_attendance (person_id, group_meeting_id)');
        $this->addSql('CREATE TABLE group_meeting (id CHAR(36) NOT NULL --(DC2Type:guid)
        , name VARCHAR(255) NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE backend_sync_upload (id CHAR(36) NOT NULL --(DC2Type:guid)
        , timestamp DATETIME NOT NULL, type VARCHAR(255) NOT NULL, exists_on_backend BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person (id CHAR(36) NOT NULL --(DC2Type:guid)
        , first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE measurements (id CHAR(36) NOT NULL --(DC2Type:guid)
        , timestamp DATETIME NOT NULL, group_meeting_attendance_id INTEGER NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE child_measurements_weight (id CHAR(36) NOT NULL --(DC2Type:guid)
        , value DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE backend_sync_download (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, last_success_general DATETIME DEFAULT NULL, last_try_general DATETIME DEFAULT NULL, last_success_authority DATETIME DEFAULT NULL, last_try_authority DATETIME DEFAULT NULL, last_id_general INTEGER DEFAULT NULL, last_id_authority INTEGER DEFAULT NULL)');
        $this->addSql('CREATE TABLE adult (id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE child_measurements_height (id CHAR(36) NOT NULL --(DC2Type:guid)
        , value DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE child_measurements_photo (id CHAR(36) NOT NULL --(DC2Type:guid)
        , file VARCHAR(255) NOT NULL, remote_uri VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE caregiver (id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE child (id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mother (id CHAR(36) NOT NULL --(DC2Type:guid)
        , birthday_estimated BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE relationship (id CHAR(36) NOT NULL --(DC2Type:guid)
        , timestamp DATETIME NOT NULL, child_id CHAR(36) NOT NULL --(DC2Type:guid)
        , adult_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(id))');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE group_meeting_attendance');
        $this->addSql('DROP TABLE group_meeting');
        $this->addSql('DROP TABLE backend_sync_upload');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE measurements');
        $this->addSql('DROP TABLE child_measurements_weight');
        $this->addSql('DROP TABLE backend_sync_download');
        $this->addSql('DROP TABLE adult');
        $this->addSql('DROP TABLE child_measurements_height');
        $this->addSql('DROP TABLE child_measurements_photo');
        $this->addSql('DROP TABLE caregiver');
        $this->addSql('DROP TABLE child');
        $this->addSql('DROP TABLE mother');
        $this->addSql('DROP TABLE relationship');
    }
}
