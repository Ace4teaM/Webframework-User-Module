/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  Prepare la base de données à recevoir le module utilisateur
*/

DROP SCHEMA IF EXISTS wfw_user CASCADE;
CREATE SCHEMA wfw_user;
set search_path to 'wfw_user';
