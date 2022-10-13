<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220825130750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE gift (
                id INT AUTO_INCREMENT NOT NULL, 
                gift_list_id INT NOT NULL, 
                title VARCHAR(255) NOT NULL, 
                description LONGTEXT NOT NULL, 
                link VARCHAR(255) DEFAULT NULL, 
                uuid VARCHAR(255) NOT NULL, 
                INDEX IDX_A47C990D51F42524 (gift_list_id), 
                PRIMARY KEY(id)) 
            DEFAULT CHARACTER 
            SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('
            CREATE TABLE gift_list (
                id INT AUTO_INCREMENT NOT NULL, 
                is_published TINYINT(1) NOT NULL, 
                uuid VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)) 
            DEFAULT CHARACTER 
            SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('
            CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL,
                recieve_gift_from_id INT DEFAULT NULL,
                offer_gift_to_id INT DEFAULT NULL,
                gift_list_id INT DEFAULT NULL,
                uuid VARCHAR(180) NOT NULL,
                roles LONGTEXT NOT NULL,
                password VARCHAR(255) NOT NULL,
                firstname VARCHAR(50) NOT NULL,
                lastname VARCHAR(50) NOT NULL,
                last_connection_at DATETIME DEFAULT NULL, 
                is_active TINYINT(1) NOT NULL,
                email VARCHAR(255) NOT NULL, 
                UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), 
                UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), 
                UNIQUE INDEX UNIQ_8D93D6496EBA165A (recieve_gift_from_id), 
                UNIQUE INDEX UNIQ_8D93D649B9B7D90A (offer_gift_to_id), 
                UNIQUE INDEX UNIQ_8D93D64951F42524 (gift_list_id), 
                PRIMARY KEY(id))
            DEFAULT CHARACTER 
            SET utf8mb4 
            COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D51F42524 FOREIGN KEY (gift_list_id) REFERENCES gift_list (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496EBA165A FOREIGN KEY (recieve_gift_from_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B9B7D90A FOREIGN KEY (offer_gift_to_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64951F42524 FOREIGN KEY (gift_list_id) REFERENCES gift_list (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gift DROP FOREIGN KEY FK_A47C990D51F42524');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496EBA165A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B9B7D90A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64951F42524');
        $this->addSql('DROP TABLE gift');
        $this->addSql('DROP TABLE gift_list');
        $this->addSql('DROP TABLE user');
    }
}
