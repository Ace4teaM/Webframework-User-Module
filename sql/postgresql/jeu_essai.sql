

/*---------------------------------------------------------------------------
 UTILISATEURS
----------------------------------------------------------------------------*/

/* Comptes */
select user_create_account('toto', 'nikita', NULL, 'avaace@hotmail.fr');
select user_create_account('AceTeaM', 'popodelasuper', NULL, 'contact@aceteam.org');
select user_create_account('admin', 'root50110', NULL, 'dev@aceteam.org');

/* Identité */
select user_make_identity('toto', 'thomas', 'auguey', '1983/12/25' ,'M');
select user_make_identity('AceTeaM', 'thomas', 'vincent', '1980/04/14' ,'M');
