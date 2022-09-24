<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220924075431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Email (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(254) NOT NULL, name VARCHAR(254) NOT NULL, password VARCHAR(254) NOT NULL, roles JSON DEFAULT NULL, createdAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, auteur VARCHAR(255) NOT NULL, bookmaker VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, is_deleted TINYINT(1) NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX IDX_64BF3F028D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coupon_image (id INT AUTO_INCREMENT NOT NULL, coupons INT DEFAULT NULL, file_name VARCHAR(254) NOT NULL, createdAt DATETIME NOT NULL, INDEX IDX_2B735BAFF5641118 (coupons), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(254) NOT NULL, name VARCHAR(254) NOT NULL, password VARCHAR(254) NOT NULL, roles JSON DEFAULT NULL, createdAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F028D93D649 FOREIGN KEY (user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE coupon_image ADD CONSTRAINT FK_2B735BAFF5641118 FOREIGN KEY (coupons) REFERENCES coupon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coupon DROP FOREIGN KEY FK_64BF3F028D93D649');
        $this->addSql('ALTER TABLE coupon_image DROP FOREIGN KEY FK_2B735BAFF5641118');
        $this->addSql('DROP TABLE Email');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE coupon_image');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
