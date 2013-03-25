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
*/

-- Renomme la table "USER" généré par PowerAMC en majuscule
ALTER TABLE wfw_user."USER" RENAME TO "user";

/*
--------------------------------------------------------------------------
     Données globales
--------------------------------------------------------------------------
*/

select wfw.set_global('AUTO_SESSION_PREFIX','_');
select wfw.set_global('CLIENT_SESSION_PREFIX','_client_session_');
select wfw.set_global('EC_CREATE_USER','U1');
select wfw.set_global('ET_CREATE_USER','Impossible de créer l''utilisateur');

/*
--------------------------------------------------------------------------
     Contraintes
--------------------------------------------------------------------------
*/
-- la table connection utilise les champs CLIENT_IP et USER_ID comme clé primaire
--alter table wfw_user.connection drop constraint PK_CONNECTION;
--alter table wfw_user.connection add constraint PK_CONNECTION primary key (CLIENT_IP, USER_ID);

-- il ne peut pas y avoir plusieurs connexions (IP) pour un meme utilisateur
ALTER TABLE wfw_user.connection ADD UNIQUE (user_id);

-- le lien symbolique est unique par connexion
ALTER TABLE wfw_user.connection ADD UNIQUE (link_path);

-- un seul mail par compte utilisateur
ALTER TABLE wfw_user.user ADD UNIQUE (user_mail);

