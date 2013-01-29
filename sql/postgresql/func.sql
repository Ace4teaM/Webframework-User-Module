/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  PostgreSQL v8.3 (version minimum requise)
*/

/*
  Supprime un utilisateur
  Remarques:
    Les sessions automatiques sont automatiquements supprimées (on_disconnect_user)
*/
CREATE OR REPLACE FUNCTION user_delete_account(p_user_id user_account.user_account_id%type)
RETURNS RESULT
AS $$
declare
	v_result RESULT;
BEGIN
     --supprime l'user_account
     delete from user_account where user_account_id = p_user_id;
     --supprime les connexions
     delete from user_connection where user_account_id = p_user_id;
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
     [RESULT] Un des résultats suivant:
        'ERR_OK     : USER_CREATED'   -> L'Utilisateur à été créé avec succès
        'ERR_FAILED : USER_EXISTS'    -> L'Utilisateur existe déjà
        'ERR_SYSTEM : NOT_SPECIFIED'  -> Une erreur système est survenue

*/

CREATE OR REPLACE FUNCTION user_create_account(
       p_user_id user_account.user_account_id%type,
       p_user_pwd user_account.user_pwd%type,
       p_client_id user_account.client_id%type,
       p_user_mail user_account.user_mail%type
)
RETURNS RESULT AS
$$
DECLARE
	v_result RESULT;
	v_cnt INT;
BEGIN

  /* verifie si le nom d'utilisateur est déjà utilisé */
  select count(*) into v_cnt from user_account where upper(user_account_id) = upper(p_user_id);
  if v_cnt > 0 then
    select 'ERR_FAILED', 'USER_NAME_EXISTS' into v_result;
    return v_result;
  end if;

  /* verifie si l'adresse mail est déjà utilisé */
  select count(*) into v_cnt from user_account where upper(user_mail) = upper(p_user_mail);
  if v_cnt > 0 then
    select 'ERR_FAILED', 'USER_MAIL_EXISTS' into v_result;
    return v_result;
  end if;

  /* insert l'entree */
  insert into user_account (user_account_id,user_pwd,client_id,user_mail) values(p_user_id,p_user_pwd,p_client_id,lower(p_user_mail));
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
$$
LANGUAGE plpgsql;

/*
  Inscrit un utilisateur
  Retourne:
     [RESULT] Un des résultats suivant:
        'ERR_OK     : USER_CREATED'   -> L'Utilisateur à été créé avec succès
        'ERR_FAILED : USER_EXISTS'    -> L'Utilisateur existe déjà
        'ERR_SYSTEM : NOT_SPECIFIED'  -> Une erreur système est survenue

*/

CREATE OR REPLACE FUNCTION user_register_account(
       p_user_id user_account.user_account_id%type,
       p_user_mail user_account.user_mail%type
)
RETURNS RESULT AS
$$
DECLARE
	v_result RESULT;
        v_token CHAR(8);
	v_cnt INT;
BEGIN

  /* verifie si le nom d'utilisateur est déjà utilisé */
  select count(*) into v_cnt from user_account where upper(user_account_id) = upper(p_user_id);
  if v_cnt > 0 then
    select 'ERR_FAILED', 'USER_NAME_EXISTS' into v_result;
    return v_result;
  end if;

  /* verifie si l'adresse mail est déjà utilisé par un compte utilisateur */
  select count(*) into v_cnt from user_account where upper(user_mail) = upper(p_user_mail);
  if v_cnt > 0 then
    select 'ERR_FAILED', 'USER_MAIL_EXISTS' into v_result;
    return v_result;
  end if;

  /* verifie si l'adresse mail est déjà utilisé pour une inscription */
  select count(*) into v_cnt from user_registration where upper(user_mail) = upper(p_user_mail);
  if v_cnt > 0 then
    select 'ERR_FAILED', 'USER_MAIL_REGISTRED' into v_result;
    return v_result;
  end if;

  /* Génère le code d’activation */
  select user_random_token() into v_token;

  /* insert l'entree */
  insert into user_registration (user_registration_id,user_token,user_id,user_mail)
        values(
            (select coalesce(max(user_registration_id),0)+1 from user_registration), /* id auto-increment */
            v_token, /* token généré */
            p_user_id,
            lower(p_user_mail)
        );
  select 'ERR_OK', 'USER_REGISTRED' into v_result;
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
$$
LANGUAGE plpgsql;

/*
  Active un compte utilisateur
*/

CREATE OR REPLACE FUNCTION user_activate_account(
       p_user_id user_account.user_account_id%type,
       p_user_pwd user_account.user_pwd%type,
       p_user_mail user_account.user_mail%type,
       p_user_token user_registration.user_token%type
)
RETURNS RESULT AS
$$
DECLARE
	v_result  RESULT;
	v_cnt     INT;
        v_reg_id  user_registration.user_registration_id%type;
BEGIN

  /* verifie si le nom d'utilisateur ou le mail est déjà utilisé */
  select count(*) into v_cnt from user_account where upper(user_account_id) = upper(p_user_id) or upper(user_mail) = upper(p_user_mail);
  if v_cnt > 0 then
    select 'ERR_FAILED', 'USER_EXISTS' into v_result;
    return v_result;
  end if;

  /* verifie si l'adresse mail est déjà utilisé pour une inscription */
  select user_registration_id into v_reg_id from user_registration where upper(user_id) = upper(p_user_id) and upper(user_mail) = upper(p_user_mail) and user_token = p_user_token;
  if v_cnt is null then
    select 'ERR_FAILED', 'USER_REGISTRATIION_NOT_EXISTS' into v_result;
    return v_result;
  end if;

  /* insert l'entree */
  select user_create_account(p_user_id, p_user_pwd, NULL, p_user_mail) into v_result;
  if v_result.err_code <> 'ERR_OK' then
    return v_result;
  end if;

  /* supprime l'inscription */
  delete from user_registration where user_registration_id = v_reg_id;

  /* ok */
  select 'ERR_OK', 'USER_CREATED' into v_result;
  return v_result;

EXCEPTION

  when others then
       select 'ERR_SYSTEM', 'NOT_SPECIFIED' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;

END;
$$
LANGUAGE plpgsql;

/*
  Génère un token aléatoire (utilisé par la fonction user_create_account)
  Retourne:
     [TEXT] Token généré

*/

CREATE OR REPLACE FUNCTION user_random_token()
RETURNS text AS 
$$
DECLARE
  chars text[] := '{0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z}';
  result text := '';
  i integer := 0;
  token_length integer := 8;
  chars_length integer := 62; /*array_length(chars, 1); PG-8.4! */
BEGIN
  for i in 1..token_length loop
    result := result || chars[round(random()*(chars_length-1))];
  end loop;
  return result;
END;
$$
LANGUAGE plpgsql;

/*
  Crée une user_session
  Retourne:
     [BOOL] TRUE si la user_session à été créée.
*/
CREATE OR REPLACE FUNCTION user_create_session(
       p_session_id user_session.user_session_id%type,
       p_local_path user_session.local_path%type
)
RETURNS BOOLEAN
AS $$
BEGIN
  insert into user_session (user_session_id,local_path) values(p_session_id,p_local_path);
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
CREATE OR REPLACE FUNCTION user_connect(
       p_user_id user_account.user_account_id%type,
       p_client_ip user_connection.client_ip%type,
       p_session_id user_session.user_session_id%type,
       p_life_time user_connection.life_time%type/* default 10000*/
       )
    returns BOOLEAN
  as $$
  declare
    v_n integer;
  begin

    /* ajoute/actualise la connexion */
    select count(*) into v_n from user_connection where client_ip = p_client_ip AND user_account_id = p_user_id;
    if v_n = 0 then
      insert into user_connection (client_ip,user_account_id,user_session_id,life_time)
        values(p_client_ip,p_user_id,p_session_id,p_life_time);
    else
      update user_connection
        set user_account_id=p_user_id, user_session_id=p_session_id, life_time=p_life_time
        where client_ip = p_client_ip AND user_account_id = p_user_id;
    end if;
  
    /* return */
    RETURN true;
  end;
$$ LANGUAGE plpgsql;

  /*
    Crée une connexion utilisateur sur une user_session client
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
      p_client_ip  : IP du client (IPv4)
      p_data_id    : Identifiant du dossier client contenant les données utilisateur
      p_data_path  : Chemin d'accès aux données utilisateurs
      p_life_time  : temps de vie de la connexion (en secondes)
    Remarque:
      Si la connexion existe deja pour cette IP, elle est actualisée.
    Retourne:
      [VARCHAR2] Identifiant de la user_session active.
  */
CREATE OR REPLACE FUNCTION user_connect_client(
       p_user_id    user_account.user_account_id%type,
       p_client_ip  user_connection.client_ip%type,
       p_data_id    varchar,
       p_data_path  varchar,
       p_life_time  user_connection.life_time%type/* default 10000*/
       )
    returns user_session.user_session_id%TYPE
  as $$
 declare
    v_n integer;
    v_SessionID user_session.user_session_id%TYPE := (get_global('CLIENT_SESSION_PREFIX')||p_data_id);
    
  begin
    ------------------------------------------------------------
    --  Ajoute la user_session
    --  Si la user_session existe elle est remplacée
    ------------------------------------------------------------
    select count(*) into v_n from user_session where user_session_id = v_SessionID;
    if v_n = 0 then
--	  msg('insert une user_session '||v_SessionID);
      insert into user_session (user_session_id,local_path)
        values(v_SessionID,p_data_path);
    else
--	  msg('actualise la user_session '||user_session_id);
      update user_session
        set local_path=p_data_path
        where user_session_id = v_SessionID;
    end if;
    

    ------------------------------------------------------------
    --  Ajoute la connexion
    --  Si la connexion existe elle est remplacée
    ------------------------------------------------------------
    select count(*) into v_n from user_connection where client_ip = p_client_ip AND user_account_id = p_user_id;
    if v_n = 0 then
--	  msg('insert la user_connection '||client_ip||'::'||user_account_id);
      insert into user_connection (client_ip,user_account_id,user_session_id,life_time)
        values(p_client_ip,p_user_id,v_SessionID,p_life_time);
    else
--	  msg('actualise la user_connection '||client_ip||'::'||user_account_id);
      update user_connection
        set user_account_id=p_user_id, user_session_id=v_SessionID, life_time=p_life_time
        where client_ip = p_client_ip AND user_account_id = p_user_id;
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
CREATE OR REPLACE FUNCTION user_disconnect_all(
       p_b_session in boolean/* default true*/
       )
RETURNS VOID
AS $$
  BEGIN
    /* supprime toutes les connexions */
    delete from user_connection;
    /* supprime toutes les sessions */
    if p_b_session = true then
      delete from user_session;
    end if;
  END;
$$ LANGUAGE plpgsql;
  
  
  /*
    Termine toutes les connexions créées avec une user_session client
    Remarques:
      Les sessions sont supprimées.
  */
CREATE OR REPLACE FUNCTION user_disconnect_all_client(
       p_b_session in boolean/* default true*/
       )
RETURNS VOID
AS $$
BEGIN
  /* supprime toutes les connexions */
  delete from user_connection where user_session_id like(get_global('CLIENT_SESSION_PREFIX')||'%');
  /* supprime toutes les sessions utilisateurs */
  if p_b_session = true then
    delete from user_session where user_session_id like(get_global('CLIENT_SESSION_PREFIX')||'%');
  end if;
END;
$$ LANGUAGE plpgsql;


  /*
    Ferme une user_session
    Parametres:
      p_session_id : identificateur de la user_session a terminer
      p_b_session  : Force la fermeture des connexions si la user_session est encore utilisée
  */
CREATE OR REPLACE FUNCTION user_delete_session(
       p_session_id user_session.user_session_id%type,
       p_force in boolean/* default true*/
       )
RETURNS VOID
AS $$
   DECLARE
   v_num_session_opened integer;
  BEGIN
    /* supprime toutes les connexions */
    select count(*) into v_num_session_opened from connexion where user_session_id = p_session_id;
    if (p_force = true) or (v_num_session_opened = 0) then
        delete from user_session where user_session_id = p_session_id;
        delete from user_connection where user_session_id = p_session_id;
    end if;
  END;
$$ LANGUAGE plpgsql;
  
  
  /*
    Termine une connexion
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
      p_client_ip  : IP de connexion
    Remarques:
      Si la user_session est de type automatique et non utilisée, elle sera detruite.
  */
CREATE OR REPLACE FUNCTION user_disconnect(
       p_user_id    user_account.user_account_id%type
       )
    returns VOID
AS $$
  DECLARE
   v_conn user_connection%rowtype;
  BEGIN
    -- obtient la connexion
    select * into v_conn from user_connection where user_account_id = p_user_id;
    -- supprime la connexion
    delete from user_connection where user_account_id = v_conn.user_account_id;
  END;
$$ LANGUAGE plpgsql;
  

/*
  Lors de la suppression d'une connexion
       * ferme les sessions automatiques non utilisées
*/  
CREATE OR REPLACE FUNCTION on_disconnect_user (
      /*p_session_id  user_session.user_session_id%type,
       p_b_force     boolean default true
      */ )
RETURNS TRIGGER 
AS $$     
begin
    --supprime la user_session si elle n'est plus utilisées
    if substr(old.user_session_id,1,1) = '_' then
       delete from user_session
           where
                user_session_id = old.user_session_id
                and (select count(*) from user_connection c where c.user_session_id = user_session_id) = 0;
    end if;
    return old;
end;
$$ LANGUAGE plpgsql;

/*
  Delencheur: tg_disconnect_user
*/
drop trigger if exists tg_disconnect_user on user_connection;
create trigger tg_disconnect_user
       after delete
       on user_connection for each row
       execute procedure on_disconnect_user();


/*
  Supprime les sessions inutilisées
*/
CREATE OR REPLACE FUNCTION user_close_unused_session(
       p_b_auto_only boolean/* default true*/
)
RETURNS VOID
AS $$
BEGIN
     if p_b_auto_only = true then
        delete from user_session
           where user_session_id like(E'\\_%')
                and (select count(*) from user_connection c where c.user_session_id = user_session_id) = 0;
     else
        delete from user_session
           where (select count(*) from user_connection c where c.user_session_id = user_session_id) = 0;
     end if;
END;
$$ LANGUAGE plpgsql;

  /*
    Définit l'identité d'un utilisateur
    Parametres:
      p_user_account_id    : Identifiant de l'utilisateur
      p_first_name         : ...
      p_last_name          : ...
      p_birth_day          : ...
      p_sex                : ...
    Remarque:
      Si aucune entrée de la table USER_IDENTITY n'existe, elle est créé
    Succès:
      IDENTITY_INSERTED : Une nouvelle identité à été créée
      IDENTITY_UPDATED  : L'identité existante à été modifié
    Echec:
      [SQL Exceptions]
  */
CREATE OR REPLACE FUNCTION user_make_identity(
       p_user_account_id    user_account.user_account_id%type,
       p_first_name         user_identity.first_name%type,
       p_last_name          user_identity.last_name%type,
       p_birth_day          user_identity.birth_day%type,
       p_sex                user_identity.sex%type
       )
    returns RESULT
  as $$
  declare
    v_user_identity_id   user_identity.user_identity_id%type;
    v_result RESULT;
  begin

    /* ajoute/actualise la connexion */
    select user_identity_id into v_user_identity_id from user_account where user_account_id = p_user_account_id;
    if v_user_identity_id is NULL then
        /* obtient un nouvel ID */
        select coalesce(max(user_identity_id)+1,1) into v_user_identity_id from user_identity;
        /* insert une nouvelle identite */
        insert into user_identity (user_identity_id,first_name,last_name,birth_day,sex)
          values(v_user_identity_id,p_first_name,p_last_name,p_birth_day,p_sex);
        /* actualise le lien avec le compte */
        update user_account
          set user_identity_id=v_user_identity_id
          where user_account_id = p_user_account_id;
          select 'ERR_OK', 'IDENTITY_INSERTED' into v_result;
    else
        /* actualise l'identite existante */
        update user_identity
          set first_name=p_first_name, last_name=p_last_name, birth_day=p_birth_day, sex=p_sex
          where user_identity_id = v_user_identity_id;
        select 'ERR_OK', 'IDENTITY_UPDATED' into v_result;
    end if;
  
    /* return */
     return v_result;
  end;
$$ LANGUAGE plpgsql;