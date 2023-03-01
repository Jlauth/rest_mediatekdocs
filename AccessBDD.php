<?php

include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {
    public $login = "root";
    public $mdp = "root";
    public $bd = "mediatek86";
    public $serveur = "127.0.0.1";
    public $port = "3306";
    public $conn = null;

    /**
     * Constructeur : demande de connexion à la BDD
     */
    public function __construct() {
        try {
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table) {
        if ($this->conn != null) {
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "exemplaire" :
                    return $this->selectAllExemplairesRevue();
                default:
                    // cas d'un select portant sur une table simple, avec tri sur le libellé
                    return $this->selectAllTableSimple($table);
            }
        } else {
            return null;
        }
    }

    /**
     * Récupération d'une ligne d'une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à récupérer
     * @return ligne de la requete correspondant à l'id
     */
    public function selectOne($table, $id) {
        if ($this->conn != null) {
            switch ($table) {
                case "exemplaire" :
                    return $this->selectAllExemplairesRevue($id);
                case "commandedocument" :
                    return $this->selectAllCommandesLivre($id);
                default:
                    // cas d'un select portant sur une table simple			
                    $param = array(
                        "id" => $id
                    );
                    return $this->conn->query("select * from $table where id=:id;", $param);
            }
        } else {
            return null;
        }
    }

    /**
     * Récupération de toutes les lignes de d'une table simple (sans jointure) avec tri sur le libellé
     * @param type $table
     * @return lignes de la requete
     */
    public function selectAllTableSimple($table) {
        $req = "select * from $table order by libelle;";
        return $this->conn->query($req);
    }

    /**
     * Récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres() {
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * Récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd() {
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * Récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues() {
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * Récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectAllExemplairesRevue($id) {
        $param = ["id" => $id];
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id = :id ";
        $req .= "order by e.dateAchat DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * Récupération de toutes les commandes d'un livre
     * @param type $id id de la commande livre
     * @return lignes de la requête
     */
    public function selectAllCommandesLivre($id) {
        $param = ["idDocument" => $id];
        $req = "Select c.id, c.dateCommande, c.montant, cd.nbExemplaire, cd.idSuivi, cd.idLivreDvd, s.libelle ";
        $req .= "from commande c ";
        $req .= "join commandedocument cd on c.id=cd.id ";
        $req .= "join suivi s on cd.idSuivi=s.id ";
        $req .= "where cd.id=c.id ";
        $req .= "order by c.dateCommande DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * Insertion simple
     * @param type $table
     * @param type $champs
     * @return true si l'ajout a fonctionné
     */
    public function insertSimple($table, $champs) {
        if ($this->conn != null && $champs != null) {
            // construction de la requête
            $req = "insert into $table (";
            foreach ($champs as $key => $value) {
                $req .= "$key,";
            }
            // (enlève la dernière virgule)
            $req = substr($req, 0, strlen($req) - 1);
            $req .= ") values (";
            foreach ($champs as $key => $value) {
                $req .= ":$key,";
            }
            // (enlève la dernière virgule)
            $req = substr($req, 0, strlen($req) - 1);
            $req .= ");";
            return $this->conn->execute($req, $champs);
        } else {
            return null;
        }
    }

    /**
     * Ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOne($table, $champs) {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "livre" :
                    return $this->insertLivre($champs);
                case "dvd" :
                    return $this->insertDvd($champs);
                case "revue" :
                    return $this->insertRevue($champs);
                case "commandelivre" :
                    return $this->insertCommandeLivre($champs);
                default:
                    // cas d'un insert portant sur une table simple
                    return $this->insertSimple($table, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * Insertion dans livre
     * @param type $champs
     */
    public function insertLivre($champs) {
        // tableau associatif des données document
        $champsDocument = [
            "Id" => $champs["Id"],
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->insertSimple("document", $champsDocument);

        // tableau associatif des données livres_dvd
        $champsLivresDvd = ["Id" => $champs["Id"]];
        $resultLivresDvd = $this->insertSimple("livres_dvd", $champsLivresDvd);

        // tableau associatif des données livre
        $champsLivre = [
            "Id" => $champs["Id"],
            "Isbn" => $champs["Isbn"],
            "Auteur" => $champs["Auteur"],
            "Collection" => $champs["Collection"]
        ];
        $resultLivre = $this->insertSimple("livre", $champsLivre);

        return $resultDocument && $resultLivresDvd && $resultLivre;
    }

    /**
     * Insertion dans lives_dvd
     * @param type $champs
     */
    public function insertDvd($champs) {
        // tableau associatif des données document
        $champsDocument = [
            "Id" => $champs["Id"],
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->insertSimple("document", $champsDocument);

        // tableau associatif des données livres_dvd
        $champsLivresDvd = ["Id" => $champs["Id"]];
        $resultLivresDvd = $this->insertSimple("livres_dvd", $champsLivresDvd);

        // tableau associatif des données dvd
        $champsDvd = [
            "Id" => $champs["Id"],
            "Duree" => $champs["Duree"],
            "Realisateur" => $champs["Realisateur"],
            "Synopsis" => $champs["Synopsis"]
        ];
        $resultDvd = $this->insertSimple("dvd", $champsDvd);

        return $resultDocument && $resultLivresDvd && $resultDvd;
    }

    /**
     * Ajout d'une commande de type livre
     * @param type $champs non et valeur de chaque champs
     */
    public function insertCommandeLivre($champs) {
        // tableau associatif des données de commande
        $champsCommande = [
            "Id" => $champs["Id"],
            "DateCommande" => $champs["DateCommande"],
            "Montant" => $champs["Montant"]
        ];
        $resultCommande = $this->insertSimple("commande", $champsCommande);

        // tableau associatif des données commande document (ici livre)
        $champsCommandeDocument = [
            "Id" => $champs["Id"],
            "NbExemplaire" => $champs["NbExemplaire"],
            "IdLivreDvd" => $champs["IdLivreDvd"],
            "IdSuivi" => $champs["IdSuivi"]
        ];
        $resultCommandeDocument = $this->insertSimple("commandedocument", $champsCommandeDocument);

        return $resultCommande && $resultCommandeDocument;
    }

    /**
     * Ajout d'une revue
     * @param type $champs nom et valeur de chaque champs
     */
    public function insertRevue($champs) {
        // tableau associatif des données document
        $champsDocument = [
            "Id" => $champs["Id"],
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->insertSimple("document", $champsDocument);

        // tableau associatif des données revue
        $champsRevue = [
            "Id" => $champs["Id"],
            "Periodicite" => $champs["Periodicite"],
            "DelaiMiseADispo" => $champs["DelaiMiseADispo"]
        ];
        $resultRevue = $this->insertSimple("revue", $champsRevue);

        return $resultDocument && $resultRevue;
    }

    /**
     * Modification d'une ligne dans une table
     * @param type $table
     * @param type $id
     * @param type $champs
     * @return true si la modification a fonctionné
     */
    public function updateSimple($table, $id, $champs) {
        if ($this->conn != null && $champs != null) {
            // construction de la requête
            $req = "update $table set ";
            foreach ($champs as $key => $value) {
                $req .= "$key=:$key,";
            }
            // (enlève la dernière virgule)
            $req = substr($req, 0, strlen($req) - 1);
            $champs["Id"] = $id;
            $req .= " where Id=:Id;";
            return $this->conn->execute($req, $champs);
        } else {
            return null;
        }
    }

    /**
     * Modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @return true si la modification a fonctionné
     */
    public function updateOne($table, $id, $champs) {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "livre" :
                    return $this->updateLivre($id, $champs);
                case "dvd" :
                    return $this->updateDvd($id, $champs);
                case "revue" :
                    return $this->updateRevue($id, $champs);
                default:
                    // cas d'un insert portant sur une table simple
                    return $this->updateSimple($table, $id, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * Modification d'un livre
     * @param type $id
     * @param type $champs
     */
    public function updateLivre($id, $champs) {
        // tableau associatif des données document
        $champsDocument = [
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->updateSimple("document", $id, $champsDocument);

        // tableau associatif des données livre
        $champsLivre = [
            "Isbn" => $champs["Isbn"],
            "Auteur" => $champs["Auteur"],
            "Collection" => $champs["Collection"]
        ];
        $resultLivre = $this->updateSimple("livre", $id, $champsLivre);

        return $resultDocument && $resultLivre;
    }

    /**
     * Modification d'un dvd
     * @param type $id
     * @param type $champs
     */
    public function updateDvd($id, $champs) {
        // tableau associatif des données document
        $champsDocument = [
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->updateSimple("document", $id, $champsDocument);

        // tableau associatif des données livre
        $champsDvd = [
            "Duree" => $champs["Duree"],
            "Realisateur" => $champs["Realisateur"],
            "Synopsis" => $champs["Synopsis"]
        ];
        $resultDvd = $this->updateSimple("dvd", $id, $champsDvd);

        return $resultDocument && $resultDvd;
    }

    /**
     * Modification d'une revue
     * @param type $id
     * @param type $champs
     */
    public function updateRevue($id, $champs) {
        // tableau associatif des données document
        $champsDocument = [
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->updateSimple("document", $id, $champsDocument);

        // tableau associatif des données livre
        $champsRevue = [
            "Periodicite" => $champs["Periodicite"],
            "DelaiMiseADispo" => $champs["DelaiMiseADispo"]
        ];
        $resultRevue = $this->updateSimple("revue", $id, $champsRevue);

        return $resultDocument && $resultRevue;
    }

    public function deleteSimple($table, $champs) {
        if ($this->conn != null) {
            // construction de la requête
            $requete = "delete from $table where ";
            foreach ($champs as $key => $value) {
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete) - 5);
            return $this->conn->execute($requete, $champs);
        } else {
            return null;
        }
    }

    /**
     * Suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */
    public function deleteOne($table, $champs) {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "livre" :
                    return $this->deleteLivre($champs);
                case "dvd" :
                    return $this->deleteDvd($champs);
                case "revue" :
                    return $this->deleteRevue($champs);
                default:
                    // cas d'un insert portant sur une table simple
                    return $this->deleteSimple($table, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * Suppression d'un livre
     * @param type $champs nom et valeur de chaque champs
     */
    public function deleteLivre($champs) {
        // tableau associatif des données livre
        $champsLivre = [
            "Id" => $champs["Id"],
            "Isbn" => $champs["Isbn"],
            "Auteur" => $champs["Auteur"],
            "Collection" => $champs["Collection"]
        ];
        $resultLivre = $this->deleteSimple("livre", $champsLivre);

        // tableau associatif des données livres_dvd
        $champsLivresDvd = ["Id" => $champs["Id"]];
        $resultLivresDvd = $this->deleteSimple("livres_dvd", $champsLivresDvd);

        // tableau associatif des données document
        $champsDocument = [
            "Id" => $champs["Id"],
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->deleteSimple("document", $champsDocument);

        return $resultLivre && $resultLivresDvd && $resultDocument;
    }

    /**
     * Suppression d'un dvd
     * @param type $champs nom et valeur de chaque champs
     */
    public function deleteDvd($champs) {
        // tableau associatif des données livre
        $champsDvd = [
            "Id" => $champs["Id"],
            "Duree" => $champs["Duree"],
            "Realisateur" => $champs["Realisateur"],
            "Synopsis" => $champs["Synopsis"]
        ];
        $resultDvd = $this->deleteSimple("dvd", $champsDvd);

        // tableau associatif des données livres_dvd
        $champsLivresDvd = ["Id" => $champs["Id"]];
        $resultLivresDvd = $this->deleteSimple("livres_dvd", $champsLivresDvd);

        // tableau associatif des données document
        $champsDocument = [
            "Id" => $champs["Id"],
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->deleteSimple("document", $champsDocument);

        return $resultDvd && $resultLivresDvd && $resultDocument;
    }

    /**
     * Suppression d'une revue
     * @param type $champs nom et valeur de chaque champs
     */
    public function deleteRevue($champs) {
        // tableau associatif des données revue
        $champsRevue = [
            "Id" => $champs["Id"],
            "Periodicite" => $champs["Periodicite"],
            "DelaiMiseADispo" => $champs["DelaiMiseADispo"]
        ];
        $resultRevue = $this->deleteSimple("revue", $champsRevue);

        // tableau associatif des données document
        $champsDocument = [
            "Id" => $champs["Id"],
            "Titre" => $champs["Titre"],
            "Image" => $champs["Image"],
            "IdRayon" => $champs["IdRayon"],
            "IdPublic" => $champs["IdPublic"],
            "IdGenre" => $champs["IdGenre"]
        ];
        $resultDocument = $this->deleteSimple("document", $champsDocument);

        return $resultRevue && $resultDocument;
    }

}
