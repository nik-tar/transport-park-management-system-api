<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251105024436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE driver (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DRIVER_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE driver_fleet_set (driver_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', fleet_set_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_66C80AF3C3423909 (driver_id), INDEX IDX_66C80AF39BF0AA28 (fleet_set_id), PRIMARY KEY(driver_id, fleet_set_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fleet_set (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', truck_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', trailer_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_49EA01EAC6957CCE (truck_id), UNIQUE INDEX UNIQ_49EA01EAB6C04CFD (trailer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_order (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', status VARCHAR(255) NOT NULL, subject_type VARCHAR(255) NOT NULL, subject_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trailer (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', plate VARCHAR(64) NOT NULL, in_service TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_C691DC4E719ED75B (plate), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE truck (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', model VARCHAR(128) NOT NULL, brand VARCHAR(128) NOT NULL, plate VARCHAR(64) NOT NULL, in_service TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_CDCCF30A719ED75B (plate), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_fleet_set ADD CONSTRAINT FK_66C80AF3C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE driver_fleet_set ADD CONSTRAINT FK_66C80AF39BF0AA28 FOREIGN KEY (fleet_set_id) REFERENCES fleet_set (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fleet_set ADD CONSTRAINT FK_49EA01EAC6957CCE FOREIGN KEY (truck_id) REFERENCES truck (id)');
        $this->addSql('ALTER TABLE fleet_set ADD CONSTRAINT FK_49EA01EAB6C04CFD FOREIGN KEY (trailer_id) REFERENCES trailer (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE driver_fleet_set DROP FOREIGN KEY FK_66C80AF3C3423909');
        $this->addSql('ALTER TABLE driver_fleet_set DROP FOREIGN KEY FK_66C80AF39BF0AA28');
        $this->addSql('ALTER TABLE fleet_set DROP FOREIGN KEY FK_49EA01EAC6957CCE');
        $this->addSql('ALTER TABLE fleet_set DROP FOREIGN KEY FK_49EA01EAB6C04CFD');
        $this->addSql('DROP TABLE driver');
        $this->addSql('DROP TABLE driver_fleet_set');
        $this->addSql('DROP TABLE fleet_set');
        $this->addSql('DROP TABLE service_order');
        $this->addSql('DROP TABLE trailer');
        $this->addSql('DROP TABLE truck');
    }
}
