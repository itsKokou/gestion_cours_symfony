<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231201021241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE declaration (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, session_id INT NOT NULL, motif VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_7AA3DAC2A76ED395 (user_id), INDEX IDX_7AA3DAC2613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enseignement (id INT AUTO_INCREMENT NOT NULL, annee_scolaire_id INT NOT NULL, professeur_id INT NOT NULL, INDEX IDX_BD310CC9331C741 (annee_scolaire_id), INDEX IDX_BD310CCBAB22EE9 (professeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enseignement_classe (enseignement_id INT NOT NULL, classe_id INT NOT NULL, INDEX IDX_678B2DF9ABEC3B20 (enseignement_id), INDEX IDX_678B2DF98F5EA509 (classe_id), PRIMARY KEY(enseignement_id, classe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enseignement_module (enseignement_id INT NOT NULL, module_id INT NOT NULL, INDEX IDX_E428B447ABEC3B20 (enseignement_id), INDEX IDX_E428B447AFC2B591 (module_id), PRIMARY KEY(enseignement_id, module_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE declaration ADD CONSTRAINT FK_7AA3DAC2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE declaration ADD CONSTRAINT FK_7AA3DAC2613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE enseignement ADD CONSTRAINT FK_BD310CC9331C741 FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire (id)');
        $this->addSql('ALTER TABLE enseignement ADD CONSTRAINT FK_BD310CCBAB22EE9 FOREIGN KEY (professeur_id) REFERENCES professeur (id)');
        $this->addSql('ALTER TABLE enseignement_classe ADD CONSTRAINT FK_678B2DF9ABEC3B20 FOREIGN KEY (enseignement_id) REFERENCES enseignement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enseignement_classe ADD CONSTRAINT FK_678B2DF98F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enseignement_module ADD CONSTRAINT FK_E428B447ABEC3B20 FOREIGN KEY (enseignement_id) REFERENCES enseignement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE enseignement_module ADD CONSTRAINT FK_E428B447AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professeur_classe DROP FOREIGN KEY FK_38ABBDC68F5EA509');
        $this->addSql('ALTER TABLE professeur_classe DROP FOREIGN KEY FK_38ABBDC6BAB22EE9');
        $this->addSql('ALTER TABLE professeur_module DROP FOREIGN KEY FK_BB082478BAB22EE9');
        $this->addSql('ALTER TABLE professeur_module DROP FOREIGN KEY FK_BB082478AFC2B591');
        $this->addSql('DROP TABLE professeur_classe');
        $this->addSql('DROP TABLE professeur_module');
        $this->addSql('ALTER TABLE absence CHANGE is_archived is_archived TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE cours ADD nbre_heure_global INT NOT NULL, ADD nbre_heure_planifie INT NOT NULL, ADD nbre_heure_restant_plan INT NOT NULL, ADD nbre_heure_realise INT NOT NULL, DROP nbre_heure, DROP nbre_heure_restant');
        $this->addSql('ALTER TABLE etudiant ADD photo VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE semestre ADD niveau_id INT NOT NULL, ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE semestre ADD CONSTRAINT FK_71688FBCB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('CREATE INDEX IDX_71688FBCB3E9C81 ON semestre (niveau_id)');
        $this->addSql('ALTER TABLE session ADD professeur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4BAB22EE9 FOREIGN KEY (professeur_id) REFERENCES professeur (id)');
        $this->addSql('CREATE INDEX IDX_D044D5D4BAB22EE9 ON session (professeur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE professeur_classe (professeur_id INT NOT NULL, classe_id INT NOT NULL, INDEX IDX_38ABBDC68F5EA509 (classe_id), INDEX IDX_38ABBDC6BAB22EE9 (professeur_id), PRIMARY KEY(professeur_id, classe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE professeur_module (professeur_id INT NOT NULL, module_id INT NOT NULL, INDEX IDX_BB082478AFC2B591 (module_id), INDEX IDX_BB082478BAB22EE9 (professeur_id), PRIMARY KEY(professeur_id, module_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE professeur_classe ADD CONSTRAINT FK_38ABBDC68F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professeur_classe ADD CONSTRAINT FK_38ABBDC6BAB22EE9 FOREIGN KEY (professeur_id) REFERENCES professeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professeur_module ADD CONSTRAINT FK_BB082478BAB22EE9 FOREIGN KEY (professeur_id) REFERENCES professeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professeur_module ADD CONSTRAINT FK_BB082478AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE declaration DROP FOREIGN KEY FK_7AA3DAC2A76ED395');
        $this->addSql('ALTER TABLE declaration DROP FOREIGN KEY FK_7AA3DAC2613FECDF');
        $this->addSql('ALTER TABLE enseignement DROP FOREIGN KEY FK_BD310CC9331C741');
        $this->addSql('ALTER TABLE enseignement DROP FOREIGN KEY FK_BD310CCBAB22EE9');
        $this->addSql('ALTER TABLE enseignement_classe DROP FOREIGN KEY FK_678B2DF9ABEC3B20');
        $this->addSql('ALTER TABLE enseignement_classe DROP FOREIGN KEY FK_678B2DF98F5EA509');
        $this->addSql('ALTER TABLE enseignement_module DROP FOREIGN KEY FK_E428B447ABEC3B20');
        $this->addSql('ALTER TABLE enseignement_module DROP FOREIGN KEY FK_E428B447AFC2B591');
        $this->addSql('DROP TABLE declaration');
        $this->addSql('DROP TABLE enseignement');
        $this->addSql('DROP TABLE enseignement_classe');
        $this->addSql('DROP TABLE enseignement_module');
        $this->addSql('ALTER TABLE absence CHANGE is_archived is_archived TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE cours ADD nbre_heure INT NOT NULL, ADD nbre_heure_restant INT NOT NULL, DROP nbre_heure_global, DROP nbre_heure_planifie, DROP nbre_heure_restant_plan, DROP nbre_heure_realise');
        $this->addSql('ALTER TABLE etudiant DROP photo');
        $this->addSql('ALTER TABLE semestre DROP FOREIGN KEY FK_71688FBCB3E9C81');
        $this->addSql('DROP INDEX IDX_71688FBCB3E9C81 ON semestre');
        $this->addSql('ALTER TABLE semestre DROP niveau_id, DROP is_active');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4BAB22EE9');
        $this->addSql('DROP INDEX IDX_D044D5D4BAB22EE9 ON session');
        $this->addSql('ALTER TABLE session DROP professeur_id');
    }
}
