/*
  (C)2012 ID-INFORMATIK, Webframework (R)
  PL/pgSQL
  Module Utilisateur (WFW_USER)
  
  PostgreSQL v8.3 (version minimum requise)
*/

/*
  Vérifie l'autentification d'un compte utilisateur
  Le nom 'p_user_id' et le mot-de-passe 'p_user_pwd' est valide la fonction réussie.
*/

CREATE OR REPLACE FUNCTION user_check_authentication(
       p_user_id user_account.user_account_id%type,
       p_user_pwd user_account.user_pwd%type
)
RETURNS RESULT AS
$$
DECLARE
	v_result     RESULT;
	v_cnt        INT;
BEGIN

  /* verifie si l'utilisateur est valide */
  select count(*) into v_cnt from user_account where upper(user_account_id) = upper(p_user_id) and user_pwd = p_user_pwd;
  if v_cnt > 0 then
    select 'ERR_OK', 'USER_EXISTS' into v_result;
    return v_result;
  end if;

  /* ok */
  select 'ERR_FAILED', 'USER_AUTHENTICATION_FAILED' into v_result;
  return v_result;

END;
$$
LANGUAGE plpgsql;

/*
  Vérifie si le compte utilisateur existe
  Si un compte utilisteur avec le nom 'p_user_id' existe la fonction réussie.
*/

CREATE OR REPLACE FUNCTION user_account_exists(
       p_user_id user_account.user_account_id%type
)
RETURNS RESULT AS
$$
DECLARE
	v_result     RESULT;
	v_cnt        INT;
BEGIN

  /* verifie si le nom d'utilisateur est déjà utilisé */
  select count(*) into v_cnt from user_account where upper(user_account_id) = upper(p_user_id);
  if v_cnt > 0 then
    select 'ERR_OK', 'USER_EXISTS' into v_result;
    return v_result;
  end if;

  /* ok */
  select 'ERR_FAILED', 'USER_NOT_EXISTS' into v_result;
  return v_result;

END;
$$
LANGUAGE plpgsql;

/*
  Vérifie si un compte utilisateur n'existe pas
  Si un compte utilisteur avec le nom 'p_user_id' ou le mail 'p_user_mail' existe la fonction échoue.
*/

CREATE OR REPLACE FUNCTION user_is_free_account(
       p_user_id user_account.user_account_id%type,
       p_user_mail user_account.user_mail%type
)
RETURNS RESULT AS
$$
DECLARE
	v_result     RESULT;
	v_cnt        INT;
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

  /* ok */
  select 'ERR_OK', 'USER_NOT_EXISTS' into v_result;
  return v_result;

END;
$$
LANGUAGE plpgsql;


/*
  Supprime un utilisateur
  Remarques:
    Les sessions automatiques sont automatiquements supprimées (on_disconnect_user)
*/
CREATE OR REPLACE FUNCTION user_delete_account(
    p_user_id user_account.user_account_id%type
)
RETURNS RESULT
AS $$
declare
	v_result RESULT;
BEGIN
     --verifie l'existance du compte
     select * from user_account_exists(p_user_id) into v_result;
     if v_result.err_code <> 'ERR_OK' then
        return v_result;
     end if;
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

  /* verifie si le mail ou l'id est déjà utilisé par un autre compte */
  select * from user_is_free_account(p_user_id, p_user_mail) into v_result;
  if v_result.err_code <> 'ERR_OK' then
    return v_result;
  end if;

  /* insert l'entree */
  insert into user_account (user_account_id,user_pwd,client_id,user_mail) values(p_user_id,p_user_pwd,p_client_id,lower(p_user_mail));
  select 'ERR_OK', 'USER_CREATED' into v_result;
  return v_result;
/*
EXCEPTION

  when unique_violation then
       select 'ERR_FAILED', 'USER_EXISTS' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
  when others then
       select 'ERR_SYSTEM', 'NOT_SPECIFIED' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
*/
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

  /* verifie si le mail ou l'id est déjà utilisé par un autre compte */
  select * from user_is_free_account(p_user_id, p_user_mail) into v_result;
  if v_result.err_code <> 'ERR_OK' then
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

  /* ok */
  select 'ERR_OK', 'USER_REGISTRED', 'TOKEN:'||v_token||';' into v_result;
  return v_result;
/*
EXCEPTION

  when unique_violation then
       select 'ERR_FAILED', 'USER_EXISTS' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
  when others then
       select 'ERR_SYSTEM', 'NOT_SPECIFIED' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
*/
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
	v_result     RESULT;
	v_cnt        INT;
        v_reg_id     user_registration.user_registration_id%type;
        v_user_token user_registration.user_token%type;
BEGIN

  /* verifie si le mail ou l'id est déjà utilisé par un autre compte */
  select * from user_is_free_account(p_user_id, p_user_mail) into v_result;
  if v_result.err_code <> 'ERR_OK' then
    return v_result;
  end if;

  /* verifie si l'inscription existe */
  select user_registration_id into v_reg_id from user_registration where upper(user_id) = upper(p_user_id) and upper(user_mail) = upper(p_user_mail);
-- RAISE NOTICE 'registration_id=%', v_reg_id;
  if v_reg_id is null then
    select 'ERR_FAILED', 'USER_REGISTRATION_NOT_EXISTS' into v_result;
    return v_result;
  end if;

  /* verifie le token d'inscription */
  select user_token into v_user_token from user_registration where user_registration_id = v_reg_id;
-- RAISE NOTICE 'user_token=%', v_user_token;
  if v_user_token <> p_user_token then
    select 'ERR_FAILED', 'USER_REGISTRATION_INVALID_TOKEN' into v_result;
    return v_result;
  end if;

  /* insert l'entree */
--  RAISE NOTICE 'user=%, pwd=%, mail=%', p_user_id, p_user_pwd, p_user_mail;
  select * from user_create_account(p_user_id, p_user_pwd, NULL, p_user_mail) into v_result;
  if v_result.err_code <> 'ERR_OK' then
    return v_result;
  end if;

  /* supprime l'inscription */
  delete from user_registration where user_registration_id = v_reg_id;

  /* ok */
  select 'ERR_OK', 'USER_CREATED' into v_result;
  return v_result;
/*
EXCEPTION

  when others then
       select 'ERR_SYSTEM', 'NOT_SPECIFIED' into v_result;
       --debugmsg(v_result.err_code||':'||v_result.err_str);
       return v_result;
*/
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
CREATE OR REPLACE FUNCTION user_connect_to_session(
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
    Crée une connexion utilisateur
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
      p_client_ip  : IP du client (IPv4)
      p_data_path  : Chemin d'accès aux données utilisateurs
      p_life_time  : temps de vie de la connexion (en secondes)
    Remarque:
      Si la connexion existe deja pour cette IP, elle est actualisée.
    Retourne:
      [VARCHAR2] Identifiant de la user_session active.
  */
CREATE OR REPLACE FUNCTION user_connect(
       p_user_id    user_account.user_account_id%type,
       p_client_ip  user_connection.client_ip%type,
       p_data_path  varchar,
       p_life_time  user_connection.life_time%type/* default 10000*/
       )
    returns RESULT
  as $$
 declare
    v_result RESULT;
    v_n integer;
    v_connection_id user_connection.user_connection_id%type;
    v_SessionID user_session.user_session_id%TYPE := (get_global('AUTO_SESSION_PREFIX',NULL)||p_user_id);
    
  begin
    ------------------------------------------------------------
    --  Ajoute la session
    --  Si la session existe elle est remplacée
    ------------------------------------------------------------
    select count(*) into v_n from user_session where user_session_id = v_SessionID;
    if v_n = 0 then
      RAISE NOTICE 'insert une session %',v_SessionID;
      insert into user_session (user_session_id,local_path)
        values(v_SessionID,p_data_path);
    else
      RAISE NOTICE 'actualise la session %',v_SessionID;
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
      RAISE NOTICE 'insert la connection %;%',p_client_ip,p_user_id;
      -- Crée l'identifiant de connexion (YYYYMMDDHHMISSTTTTTTTT)
      select to_char(current_timestamp, 'YYYYMMDDHHMISS')||user_random_token() into v_connection_id;
      --v_ConnectionID user_connection.user_connection_id%TYPE := user_random_token()||user_random_token());
      --select coalesce(max(user_connection_id),0)+1 into v_connection_id from user_connection;
      -- Crée la connexion
      insert into user_connection (user_connection_id,client_ip,user_account_id,user_session_id,life_time)
        values(v_connection_id,p_client_ip,p_user_id,v_SessionID,p_life_time);
    else
      RAISE NOTICE 'actualise la connection %;%',p_client_ip,p_user_id;
      -- Actualise la connexion
      update user_connection
        set user_account_id=p_user_id, user_session_id=v_SessionID, life_time=p_life_time
        where client_ip = p_client_ip AND user_account_id = p_user_id;
      -- Recupere l'identifiant de connexion
      select user_connection_id into v_connection_id from user_connection where client_ip = p_client_ip AND user_account_id = p_user_id;
    end if;
  
    -- OK
    select 'ERR_OK', 'USER_CONNECTED', 'CONNECTION_ID:'||v_connection_id||';' into v_result;
    RETURN v_result;
  end;
$$ LANGUAGE plpgsql;


  /*
    Termine toutes les connexions
    Parametres:
      p_b_session : Si true, termine toutes les sessions
  */
CREATE OR REPLACE FUNCTION user_disconnect_all()
RETURNS RESULT
AS $$
DECLARE
    v_result RESULT;
BEGIN
    /* supprime toutes les connexions */
    delete from user_connection;
    /* supprime toutes les sessions automatiques */
    delete from user_session where user_session_id like(get_global('AUTO_SESSION_PREFIX',NULL)||'%');

    -- ok
    select 'ERR_OK', 'USER_DISCONNECTED' into v_result;
    return v_result;
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
RETURNS RESULT
AS $$
DECLARE
    v_result RESULT;
BEGIN
  -- supprime toutes les connexions
  delete from user_connection where user_session_id like(get_global('CLIENT_SESSION_PREFIX')||'%');
  -- supprime toutes les sessions utilisateurs
  if p_b_session = true then
    delete from user_session where user_session_id like(get_global('CLIENT_SESSION_PREFIX')||'%');
  end if;

  -- ok
  select 'ERR_OK', 'USER_DISCONNECTED' into v_result;
  return v_result;
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
    Termine la connexion d'un utilisateur
    Parametres:
      p_user_id    : Identifiant de l'utilisateur
    Remarques:
      Si la session est de type automatique et non utilisée, elle sera detruite.
  */
CREATE OR REPLACE FUNCTION user_disconnect_account(
       p_user_id    user_account.user_account_id%type
       )
    returns RESULT
AS $$
DECLARE
    v_result RESULT;
    v_conn user_connection%rowtype;
  BEGIN
     --verifie l'existance du compte
     select * from user_account_exists(p_user_id) into v_result;
     if v_result.err_code <> 'ERR_OK' then
        return v_result;
     end if;

    -- obtient la connexion
    select * into v_conn from user_connection where user_account_id = p_user_id;

    -- supprime la connexion
    delete from user_connection where user_account_id = v_conn.user_account_id;

    -- les sessions automatiques seront supprimées par le trigger 'on_disconnect_user'
    -- delete from user_session where user_session_id like(get_global('CLIENT_SESSION_PREFIX')||p_user_id);

    -- ok
    select 'ERR_OK', 'USER_DISCONNECTED' into v_result;
    return v_result;
  END;
$$ LANGUAGE plpgsql;
  
  /*
    Termine une connexion
    Parametres:
      p_connection_id    : Identifiant de connexion
    Remarques:
      Si la session est de type automatique et non utilisée, elle sera detruite.
  */
CREATE OR REPLACE FUNCTION user_disconnect(
       p_connection_id    user_connection.user_connection_id%type
       )
    returns RESULT
AS $$
DECLARE
    v_result RESULT;
  BEGIN
    -- supprime la connexion
    delete from user_connection where user_connection_id = p_connection_id;

    -- les sessions automatiques seront supprimées par le trigger 'on_disconnect_user'
    -- delete from user_session where user_session_id like(get_global('CLIENT_SESSION_PREFIX')||p_user_id);

    -- ok
    select 'ERR_OK', 'USER_DISCONNECTED' into v_result;
    return v_result;
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
      p_user_connection    : Identifiant de connexion
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
       p_user_connection_id user_connection.user_connection_id%type,
       p_first_name         user_identity.first_name%type,
       p_last_name          user_identity.last_name%type,
       p_birth_day          user_identity.birth_day%type,
       p_sex                user_identity.sex%type
       )
    returns RESULT
  as $$
  declare
    v_user_account_id    user_account.user_account_id%type;
    v_user_identity_id   user_identity.user_identity_id%type;
    v_result RESULT;
  begin

    /* selectionne le compte utilisateur lié */
    select user_account_id into v_user_account_id from user_connection where user_connection_id = p_user_connection_id;
    if v_user_account_id is NULL then
        select 'ERR_OK', 'USER_CONNECTION_NOT_EXISTS' into v_result;
        return v_result;
    end if;

    /* ajoute/actualise l'identite */
    select user_identity_id into v_user_identity_id from user_account where user_account_id = v_user_account_id;
    if v_user_identity_id is NULL then
        /* obtient un nouvel ID */
        select coalesce(max(user_identity_id)+1,1) into v_user_identity_id from user_identity;
        /* insert une nouvelle identite */
        insert into user_identity (user_identity_id,first_name,last_name,birth_day,sex)
          values(v_user_identity_id,p_first_name,p_last_name,p_birth_day,p_sex);
        /* actualise le lien avec le compte */
        update user_account
          set user_identity_id=v_user_identity_id
          where user_account_id = v_user_account_id;
    else
        /* actualise l'identite existante */
        update user_identity
          set first_name=p_first_name, last_name=p_last_name, birth_day=p_birth_day, sex=p_sex
          where user_identity_id = v_user_identity_id;
    end if;
  
    /* return */
    select 'ERR_OK', 'USER_IDENTITY_UPDATED' into v_result;
    return v_result;
  end;
$$ LANGUAGE plpgsql;


/*
  Maintient une connexion utilisateur
  Parametres:
    p_user_connection_id  : Identifiant de connexion
    p_client_ip           : Adresse IP du client
  Résultats:
    USER_CONNECTED             L'utilisateur est connecté
    USER_CONNECTION_NOT_EXISTS La connexion n'existe pas
    USER_CONNECTION_IP_REFUSED L'adresse IP différe
  Paramètres de retour:
    EXPIRE                     Date de la prochaine expiration
    UID                        Identifiant du compte utilisateur
*/
CREATE OR REPLACE FUNCTION user_check_connection(
       p_user_connection_id    user_connection.user_connection_id%type,
       p_client_ip             user_connection.client_ip%type
       )
    returns RESULT
  as $$
  declare
    v_result RESULT;
    v_client_ip user_connection.client_ip%type := NULL;
    v_expire INT; /* timestamp de la date d'expiration */
  begin

    /* Vérifie l’existence de la connexion */
    select client_ip into v_client_ip from user_connection where user_connection_id = p_user_connection_id;
    RAISE NOTICE 'v_client_ip %',v_client_ip;
    if v_client_ip is NULL then
        select 'ERR_FAILED', 'USER_CONNECTION_NOT_EXISTS' into v_result;
        return v_result;
    end if;

    /* Vérifie l’adresse IP */
    if v_client_ip <> p_client_ip then
        select 'ERR_FAILED', 'USER_CONNECTION_IP_REFUSED' into v_result;
        return v_result;
    end if;
  
    /* Vérifie la validité de la date d'expiration 
    SELECT ROUND(EXTRACT(EPOCH FROM last_access) + life_time * 60) - ROUND(EXTRACT(EPOCH FROM now())) into v_expire from user_connection where user_connection_id = p_user_connection_id;
    if v_expire < 1 then
        select 'ERR_FAILED', 'USER_CONNECTION_EXPIRED' into v_result;
        return v_result;
    end if;*/
  
    /* Actualise la date d’accees */
    update user_connection set last_access = now() where user_connection_id = p_user_connection_id;

    /* Calcule la date d'expiration */
    /*SELECT TIMESTAMP WITH TIME ZONE 'epoch' + (EXTRACT(EPOCH FROM now()) + 3600) * INTERVAL '1 second';*/
    SELECT ROUND(EXTRACT(EPOCH FROM last_access) + life_time * 60) into v_expire from user_connection where user_connection_id = p_user_connection_id;
    /*select last_access+life_time into v_expire from user_connection where user_connection_id = p_user_connection_id;*/

    /* return */
    select 'ERR_OK', 'USER_CONNECTED', 'EXPIRE:'||v_expire||';'||'UID:'||(select user_account_id from user_connection where user_connection_id = p_user_connection_id)||';' into v_result;
    return v_result;
  end;
$$ LANGUAGE plpgsql;

