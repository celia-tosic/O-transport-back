<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220404090816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery ADD customer_id INT NOT NULL, ADD admin_id INT NOT NULL, ADD driver_id INT NOT NULL');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC109395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10C3423909 FOREIGN KEY (driver_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3781EC109395C3F3 ON delivery (customer_id)');
        $this->addSql('CREATE INDEX IDX_3781EC10642B8210 ON delivery (admin_id)');
        $this->addSql('CREATE INDEX IDX_3781EC10C3423909 ON delivery (driver_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC109395C3F3');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10642B8210');
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10C3423909');
        $this->addSql('DROP INDEX IDX_3781EC109395C3F3 ON delivery');
        $this->addSql('DROP INDEX IDX_3781EC10642B8210 ON delivery');
        $this->addSql('DROP INDEX IDX_3781EC10C3423909 ON delivery');
        $this->addSql('ALTER TABLE delivery DROP customer_id, DROP admin_id, DROP driver_id');
    }
}
