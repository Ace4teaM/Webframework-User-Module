/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  Supprime les données de la base
*/

DROP SCHEMA wfw_user cascade;


select wfw.del_global('AUTO_SESSION_PREFIX');
select wfw.del_global('CLIENT_SESSION_PREFIX');
select wfw.del_global('EC_CREATE_USER');
select wfw.del_global('ET_CREATE_USER');
