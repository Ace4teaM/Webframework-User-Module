<?php

/**
 * Définition des codes d'erreurs
 *
 * @author AUGUEY Thomas
 */
class Error {
    //contextes
    public static $code = array(
        "ERR_OK"=>"Succès",
        "ERR_FAILED"=>"Echec",
        "ERR_SYSTEM"=>"Erreur système",
        "CONFIGURATION"=>"Configuration",
        "MODULE"=>"Module",
        "DATABASE"=>"Base de données",
        "FEATURE"=>"Fonctionnalité"
        );
    //erreurs
    public static $info = array(
        "MODULE_NOT_FOUND"=>"Module introuvable",
        "DATABASE_CONNECTION_NOT_FOUND"=>"Connection introuvable",
        "UNSUPORTED_FEATURE"=>"Fonctionnalité non supportée"
    );
}

?>
