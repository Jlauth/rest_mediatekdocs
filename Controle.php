<?php
include_once("AccessBDD.php");

/**
 * Contrôleur : reçoit et traite les demandes du point d'entrée
 */
class Controle{
	
    private $accessBDD;

    /**
     * Constructeur : récupération de l'instance d'accès à la BDD
     */
    public function __construct(){
        try{
            $this->accessBDD = new AccessBDD();
        }catch(Exception $e){
            $this->reponse(500, "erreur serveur");
            die();
        }
    }

    /**
     * Réponse renvoyée (affichée) au client au format json
     * @param int $code code standard HTTP
     * @param string $message message correspondant au code
     * @param array $result résultat de la demande 
     */
    private function reponse($code, $message, $result=""){
        $retour = array(
            'code' => $code,
            'message' => $message,
            'result' => $result
        );
        echo json_encode($retour, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Requête arrivée en GET (select)
     * @param string $table nom de la table
     * @param string $id valeur de l'id
     */
    public function get($table, $id=null){
        $result = null;
        if ($id==null){
            $result = $this->accessBDD->selectAll($table);
        }else{
            $result = $this->accessBDD->selectOne($table, $id);
        }
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK", $result);
        }
    }

    /**
     * Requête arrivée en GET (select)
     * @param type $contenu de la table utilisateur
     */
    public function getUtilisateur($contenu){
        $result = $this->accessBDD->selectUtilisateur($contenu);
        if($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{
            $this->reponse(200, "OK", $result);
        }
    }
   
    /**
     * Requête arrivée en POST (insert)
     * @param string $table nom de la table
     * @param array $champs nom et valeur des champs
     */
    public function post($table, $champs){
        $result = $this->accessBDD->insertOne($table, $champs);	
        if ($result === null || $result === false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }

    /**
     * Requête arrivée en PUT (update)
     * @param string $table nom de la table
     * @param string $id valeur de l'id
     * @param array $champs nom et valeur des champs
     */
    public function put($table, $id, $champs){
        $result = $this->accessBDD->updateOne($table, $id, $champs);	
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }
	
     /**
     * Requête arrivée en DELETE
     * @param string $table nom de la table
     * @param array $champs nom et valeur des champs
     */
    public function delete($table, $champs){
        $result = $this->accessBDD->deleteOne($table, $champs);	
        if ($result == null || $result == false){
            $this->reponse(400, "requete invalide");
        }else{	
            $this->reponse(200, "OK");
        }
    }
    
    /**
     * login et/ou pwd incorrects
     */
    public function unauthorized(){
        $this->reponse(401, "authentification incorrecte");
    }
}