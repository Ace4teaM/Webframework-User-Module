/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  Supprime les données de la base
*/

select del_global('AUTO_SESSION_PREFIX');
select del_global('CLIENT_SESSION_PREFIX');
select del_global('EC_CREATE_USER');
select del_global('ET_CREATE_USER');
