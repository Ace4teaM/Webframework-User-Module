/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  Fonctions
*/

/*
  Supprime un utilisateur
  Remarques:
    Les sessions automatiques sont automatiquements supprimées (on_disconnect_user)
*/
CREATE OR REPLACE FUNCTION wfw_user.delete_user(p_user_id wfw_user.user.user_id%type)
RETURNS WFW.RESULT
AS $$
declare
	v_result WFW.RESULT;
BEGIN
     --supprime l'user
     delete from wfw_user.user where user_id = p_user_id;
     --supprime les connexions
     delete from wfw_user.connection where user_id = p_user_id;
     --termine
     select 'ERR_OK', 'USER_DELETED' into v_result;
     return v_result;
EXCEPTION
  when others then
       select 'ERR_SYSTEM', 'NOT_SPECIFIED' into v_result;
       return v_result;
END;
$$ LANGUAGE plpgsql;


/*
  Crée un utilisateur
  Retourne:
     [WFW.RESULT] Un des résultats suivant:
        'ERR_OK     : USER_CREATED'   -> L'Utilisateur à été créé avec succès
        'ERR_FAILED : USER_EXISTS'    -> L'Utilisateur existe déjà
        'ERR_SYSTEM : NOT_SPECIFIED'  -> Une erreur système est survenue

*/

CREATE OR REPLACE FUNCTION wfw_user.create_user(
       p_user_id wfw_user.user.user_id%type,
       p_user_pwd wfw_user.user.user_pwd%type,
       p_client_id wfw_user.user.client_id%type,
       p_user_mail wfw_user.user.user_mail%type
)
RETURNS WFW.RESULT
AS $$
declare
	v_result WFW.RESULT;
BEGIN
  insert into wfw_user.user (user_id,user_pwd,client_id,user_mail) values(p_user_id,p_user_pwd,p_client_id,p_user_mail);
  select 'ERR_OK', 'USER_CREATED' into v_result;
  return v_result;
EXCEPTION
  when unique_violation then
       select 'ERR_FAILED', 'USER_EXISTS' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
  when others then
       select 'ERR_SYSTEM', 'NOT_SPECIFIED' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
END;
$$ LANGUAGE plpgsql;

/*
  Crée une session
  Retourne:
     [BOOL] TRUE si la session à été créée.
*/
CREATE OR REPLACE FUNCTION wfw_user.create_session(
       p_session_id wfw_user.session.session_id%type,
       p_local_path wfw_user.session.local_path%type
)
RETURNS BOOLEAN
AS $$
BEGIN
  insert into wfw_user.session (session_id,local_path) values(p_session_id,p_local_path);
  return true;
EXCEPTION
  when unique_violation then
       return false;
END;
$$ LANGUAGE plpgsql;

  /*
    Initialise une connexion utilisateur
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
      p_client_ip  : IP du client (IPv4)
      p_session_id : Identifiant de Session
      p_life_time  : Temps de vie de la connexion (en secondes)
    Remarque:
      Si une connexion existe deja pour cette IP, elle est remplacée
  */
CREATE OR REPLACE FUNCTION wfw_user.connect(
       p_user_id wfw_user.user.user_id%type,
       p_client_ip wfw_user.connection.client_ip%type,
       p_session_id wfw_user.session.session_id%type,
       p_life_time wfw_user.connection.life_time%type default 10000
       )
    returns BOOLEAN
  as $$
  declare
    v_n integer;
  begin

    /* ajoute/actualise la connexion */
    select count(*) into v_n from wfw_user.connection where client_ip = p_client_ip AND user_id = p_user_id;
    if v_n = 0 then
      insert into wfw_user.connection (client_ip,user_id,session_id,life_time)
        values(p_client_ip,p_user_id,p_session_id,p_life_time);
    else
      update wfw_user.connection
        set user_id=p_user_id, session_id=p_session_id, life_time=p_life_time
        where client_ip = p_client_ip AND user_id = p_user_id;
    end if;
  
    /* return */
    RETURN true;
  end;
$$ LANGUAGE plpgsql;

  /*
    Crée une connexion utilisateur sur une session client
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
      p_client_ip  : IP du client (IPv4)
      p_data_id    : Identifiant du dossier client contenant les données utilisateur
      p_data_path  : Chemin d'accès aux données utilisateurs
      p_life_time  : temps de vie de la connexion (en secondes)
    Remarque:
      Si la connexion existe deja pour cette IP, elle est actualisée.
    Retourne:
      [VARCHAR2] Identifiant de la session active.
  */
CREATE OR REPLACE FUNCTION wfw_user.connect_client(
       p_user_id    wfw_user.user.user_id%type,
       p_client_ip  wfw_user.connection.client_ip%type,
       p_data_id    varchar,
       p_data_path  varchar,
       p_life_time  wfw_user.connection.life_time%type default 10000
       )
    returns wfw_user.session.session_id%TYPE
  as $$
 declare
    v_n integer;
    v_SessionID wfw_user.session.session_id%TYPE := (wfw.get_global('CLIENT_SESSION_PREFIX')||p_data_id);
    
  begin
    ------------------------------------------------------------
    --  Ajoute la session
    --  Si la session existe elle est remplacée
    ------------------------------------------------------------
    select count(*) into v_n from wfw_user.session where session_id = v_SessionID;
    if v_n = 0 then
--	  wfw.msg('insert une session '||v_SessionID);
      insert into wfw_user.session (session_id,local_path)
        values(v_SessionID,p_data_path);
    else
--	  wfw.msg('actualise la session '||session_id);
      update wfw_user.session
        set local_path=p_data_path
        where session_id = v_SessionID;
    end if;
    

    ------------------------------------------------------------
    --  Ajoute la connexion
    --  Si la connexion existe elle est remplacée
    ------------------------------------------------------------
    select count(*) into v_n from wfw_user.connection where client_ip = p_client_ip AND user_id = p_user_id;
    if v_n = 0 then
--	  wfw.msg('insert la connection '||client_ip||'::'||user_id);
      insert into wfw_user.connection (client_ip,user_id,session_id,life_time)
        values(p_client_ip,p_user_id,v_SessionID,p_life_time);
    else
--	  wfw.msg('actualise la connection '||client_ip||'::'||user_id);
      update wfw_user.connection
        set user_id=p_user_id, session_id=v_SessionID, life_time=p_life_time
        where client_ip = p_client_ip AND user_id = p_user_id;
    end if;
  
    -- return
    RETURN v_SessionID;
  end;
$$ LANGUAGE plpgsql;


  /*
    Termine toutes les connexions
    Parametres:
      p_b_session : Si true, termine toutes les sessions
  */
CREATE OR REPLACE FUNCTION wfw_user.disconnect_all(
       p_b_session in boolean default true
       )
RETURNS VOID
AS $$
  BEGIN
    /* supprime toutes les connexions */
    delete from wfw_user.connection;
    /* supprime toutes les sessions */
    if p_b_session = true then
      delete from wfw_user.session;
    end if;
  END;
$$ LANGUAGE plpgsql;
  
  
  /*
    Termine toutes les connexions créées avec une session client
    Remarques:
      Les sessions sont supprimées.
  */
CREATE OR REPLACE FUNCTION wfw_user.disconnect_all_client(
       p_b_session in boolean default true
       )
RETURNS VOID
AS $$
BEGIN
  /* supprime toutes les connexions */
  delete from wfw_user.connection where session_id like(wfw.get_global('CLIENT_SESSION_PREFIX')||'%');
  /* supprime toutes les sessions utilisateurs */
  if p_b_session = true then
    delete from wfw_user.session where session_id like(wfw.get_global('CLIENT_SESSION_PREFIX')||'%');
  end if;
END;
$$ LANGUAGE plpgsql;


  /*
    Ferme une session
    Parametres:
      p_session_id : identificateur de la session a terminer
      p_b_session  : Force la fermeture des connexions si la session est encore utilisée
  */
CREATE OR REPLACE FUNCTION wfw_user.delete_session(
       p_session_id wfw_user.SESSION.session_id%type,
       p_force in boolean default true
       )
RETURNS VOID
AS $$
   DECLARE
   v_num_session_opened integer;
  BEGIN
    /* supprime toutes les connexions */
    select count(*) into v_num_session_opened from connexion where session_id = p_session_id;
    if (p_force = true) or (v_num_session_opened = 0) then
        delete from wfw_user.session where session_id = p_session_id;
        delete from wfw_user.connection where session_id = p_session_id;
    end if;
  END;
$$ LANGUAGE plpgsql;
  
  
  /*
    Termine une connexion
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
      p_client_ip  : IP de connexion
    Remarques:
      Si la session est de type automatique et non utilisée, elle sera detruite.
  */
CREATE OR REPLACE FUNCTION wfw_user.disconnect(
       p_user_id    wfw_user.user.user_id%type
       )
    returns VOID
AS $$
  DECLARE
   v_conn wfw_user.connection%rowtype;
  BEGIN
    -- obtient la connexion
    select * into v_conn from wfw_user.connection where user_id = p_user_id;
    -- supprime la connexion
    delete from wfw_user.connection where user_id = v_conn.user_id;
  END;
$$ LANGUAGE plpgsql;
  

/*
  Lors de la suppression d'une connexion
       * ferme les sessions automatiques non utilisées
*/  
CREATE OR REPLACE FUNCTION wfw_user.on_disconnect_user (
      /*p_session_id  wfw_user.session.session_id%type,
       p_b_force     boolean default true
      */ )
RETURNS TRIGGER 
AS $$     
begin
    --supprime la session si elle n'est plus utilisées
    if substr(old.session_id,1,1) = '_' then
       delete from wfw_user.session
           where
                session_id = old.session_id
                and (select count(*) from wfw_user.connection c where c.session_id = session_id) = 0;
    end if;
    return old;
end;
$$ LANGUAGE plpgsql;

/*
  Delencheur: tg_disconnect_user
*/
drop trigger if exists tg_disconnect_user on wfw_user.connection;
create trigger tg_disconnect_user
       after delete
       on wfw_user.connection for each row
       execute procedure wfw_user.on_disconnect_user();


/*
  Supprime les sessions inutilisées
*/
CREATE OR REPLACE FUNCTION wfw_user.close_unused_session(
       p_b_auto_only boolean default true
)
RETURNS VOID
AS $$
BEGIN
     if p_b_auto_only = true then
        delete from wfw_user.session
           where session_id like(E'\\_%')
                and (select count(*) from wfw_user.connection c where c.session_id = session_id) = 0;
     else
        delete from wfw_user.session
           where (select count(*) from wfw_user.connection c where c.session_id = session_id) = 0;
     end if;
END;
$$ LANGUAGE plpgsql;
