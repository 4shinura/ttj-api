<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313075027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidat (id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, date_candidature DATE NOT NULL, statut_candidature VARCHAR(50) NOT NULL, candidat_id INT NOT NULL, offre_id INT NOT NULL, INDEX IDX_E33BD3B88D0EB82 (candidat_id), INDEX IDX_E33BD3B84CC8505A (offre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, raison_sociale_entreprise VARCHAR(50) NOT NULL, adresse_entreprise VARCHAR(200) NOT NULL, tel_entreprise INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, contenu_message VARCHAR(400) NOT NULL, date_envoi_message DATE NOT NULL, emetteur_message_id INT NOT NULL, destinataire_message_id INT NOT NULL, INDEX IDX_B6BD307FA7304184 (emetteur_message_id), INDEX IDX_B6BD307FF2EB4E1F (destinataire_message_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offre (id INT AUTO_INCREMENT NOT NULL, type_offre VARCHAR(50) NOT NULL, titre_offre VARCHAR(50) NOT NULL, description_offre VARCHAR(400) NOT NULL, date_publication_offre DATE NOT NULL, date_limite_offre DATE NOT NULL, statut_offre VARCHAR(50) NOT NULL, recruteur_offre_id INT NOT NULL, INDEX IDX_AF86866FCF07A3EE (recruteur_offre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recruteur (entreprise_recruteur_id INT NOT NULL, id INT NOT NULL, INDEX IDX_2BD3678C615F968D (entreprise_recruteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom_utilisateur VARCHAR(50) NOT NULL, prenom_utilisateur VARCHAR(50) NOT NULL, email_utilisateur VARCHAR(100) NOT NULL, mdp_utilisateur VARCHAR(50) NOT NULL, statut_utilisateur VARCHAR(50) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE candidat ADD CONSTRAINT FK_6AB5B471BF396750 FOREIGN KEY (id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B88D0EB82 FOREIGN KEY (candidat_id) REFERENCES candidat (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B84CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA7304184 FOREIGN KEY (emetteur_message_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF2EB4E1F FOREIGN KEY (destinataire_message_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866FCF07A3EE FOREIGN KEY (recruteur_offre_id) REFERENCES recruteur (id)');
        $this->addSql('ALTER TABLE recruteur ADD CONSTRAINT FK_2BD3678C615F968D FOREIGN KEY (entreprise_recruteur_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE recruteur ADD CONSTRAINT FK_2BD3678CBF396750 FOREIGN KEY (id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidat DROP FOREIGN KEY FK_6AB5B471BF396750');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B88D0EB82');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B84CC8505A');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA7304184');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF2EB4E1F');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FCF07A3EE');
        $this->addSql('ALTER TABLE recruteur DROP FOREIGN KEY FK_2BD3678C615F968D');
        $this->addSql('ALTER TABLE recruteur DROP FOREIGN KEY FK_2BD3678CBF396750');
        $this->addSql('DROP TABLE candidat');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE recruteur');
        $this->addSql('DROP TABLE utilisateur');
    }
}
