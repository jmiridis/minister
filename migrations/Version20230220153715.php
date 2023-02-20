<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230220153715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add wedding_date and location to testimonial table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE testimonial ADD wedding_date DATE DEFAULT NULL, ADD location VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE testimonial DROP wedding_date, DROP location, CHANGE updated_at updated_at DATETIME NOT NULL');
    }
}
