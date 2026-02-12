<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207180007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD course_id INT NOT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_23A0E66591CC992 ON article (course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66591CC992');
        $this->addSql('DROP INDEX IDX_23A0E66591CC992');
        $this->addSql('ALTER TABLE article DROP course_id');
    }
}
