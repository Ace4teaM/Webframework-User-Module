/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  Initialise les objets et le contenu de base avant utilisation
*/

/*
--------------------------------------------------------------------------
     Fix
--------------------------------------------------------------------------


-- Renomme la table "USER" généré par PowerAMC en majuscule
ALTER TABLE "USER" RENAME TO "user";
*/
/*
--------------------------------------------------------------------------
     Données globales
--------------------------------------------------------------------------
*/

select set_global('AUTO_SESSION_PREFIX','_');
select set_global('CLIENT_SESSION_PREFIX','_client_session_');
select set_global('EC_CREATE_USER','U1');
select set_global('ET_CREATE_USER','Impossible de créer l''utilisateur');

/*
--------------------------------------------------------------------------
     Contraintes
--------------------------------------------------------------------------
*/
-- la table connection utilise les champs CLIENT_IP et USER_ID comme clé primaire
--alter table connection drop constraint PK_CONNECTION;
--alter table connection add constraint PK_CONNECTION primary key (CLIENT_IP, USER_ID);

-- il ne peut pas y avoir plusieurs connexions (IP) pour un meme utilisateur
ALTER TABLE user_connection ADD UNIQUE (user_account_id);

-- le lien symbolique est unique par connexion
ALTER TABLE user_connection ADD UNIQUE (link_path);

-- un seul mail par compte utilisateur
ALTER TABLE user_account ADD UNIQUE (user_mail);

