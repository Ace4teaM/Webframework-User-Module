/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  Jeu de test
*/

/*
	Insert des valeurs de tests
*/
CREATE OR REPLACE FUNCTION wfw_user.test()
returns void
as $$
declare
  v_result BOOLEAN;
  v_result2 VARCHAR;
  v_result3 WFW.RESULT;
  
begin
 perform wfw_user.disconnect_all();
 v_result3 := wfw_user.delete_user('AceTeaM');
 v_result3 := wfw_user.delete_user('Popo');
 v_result3 := wfw_user.delete_user('Toto');
 v_result3 := wfw_user.create_user('AceTeaM','nikiball','AceTeaM','avaace@hotmail.fr');
 v_result3 := wfw_user.create_user('Popo','delasuper','Popo','avaace@live.fr');
 v_result3 := wfw_user.create_user('Toto','avalanche','Toto','Toto@avalanche.fr');
 v_result := wfw_user.create_session('public_upload','data/client/public_upload');
 v_result := wfw_user.create_session('456-789123','data/client/456-789123');

 v_result  := wfw_user.connect('AceTeaM','92.168.12.20','456-789123');
 v_result2 := wfw_user.connect_client('Popo','92.168.12.10','456-789123','data/client/456-789123');
-- v_result2 := wfw_user.connect_client('Toto','92.168.12.10','456-789123','data/client/456-789123');

 perform wfw_user.disconnect('AceTeaM');
 perform wfw_user.disconnect('Toto');
 
/* perform wfw_user.disconnect_all(true);*/
/* perform wfw_user.disconnect_all_client(true);*/
 
end;
$$ LANGUAGE plpgsql;

select wfw_user.test();
select * from wfw_user.connection;
