<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230219171548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, wedding_date DATE DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_4C62E638DE12AB56 (created_by), INDEX IDX_4C62E63816FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, position SMALLINT DEFAULT NULL, is_active TINYINT(1) NOT NULL, question LONGTEXT NOT NULL, answer LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_E8FF75CCDE12AB56 (created_by), INDEX IDX_E8FF75CC16FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, caption VARCHAR(255) DEFAULT NULL, description VARCHAR(1024) DEFAULT NULL, image VARCHAR(255) NOT NULL, position INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_16DB4F89DE12AB56 (created_by), INDEX IDX_16DB4F8916FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE testimonial (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, content LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, signature VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_E6BDCDF7DE12AB56 (created_by), INDEX IDX_E6BDCDF716FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63816FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE faq ADD CONSTRAINT FK_E8FF75CCDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE faq ADD CONSTRAINT FK_E8FF75CC16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F89DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8916FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE testimonial ADD CONSTRAINT FK_E6BDCDF7DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE testimonial ADD CONSTRAINT FK_E6BDCDF716FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E638DE12AB56');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E63816FE72E1');
        $this->addSql('ALTER TABLE faq DROP FOREIGN KEY FK_E8FF75CCDE12AB56');
        $this->addSql('ALTER TABLE faq DROP FOREIGN KEY FK_E8FF75CC16FE72E1');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F89DE12AB56');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8916FE72E1');
        $this->addSql('ALTER TABLE testimonial DROP FOREIGN KEY FK_E6BDCDF7DE12AB56');
        $this->addSql('ALTER TABLE testimonial DROP FOREIGN KEY FK_E6BDCDF716FE72E1');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE testimonial');
        $this->addSql('DROP TABLE user');
    }
}
