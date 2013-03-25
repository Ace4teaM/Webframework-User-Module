/*==============================================================*/
/* Nom de SGBD :  PostgreSQL 8                                  */
/* Date de création :  24/08/2012 16:26:32                      */
/*==============================================================*/
set search_path to 'wfw_user';

drop table if exists ADDRESS;

drop table if exists CONNECTION;

drop table if exists IDENTITY;

drop table if exists SESSION;

drop table if exists "USER";

drop domain if exists SEX;

drop domain if exists STREET_PREFIX;

/*==============================================================*/
/* Domaine : SEX                                                */
/*==============================================================*/
create domain SEX as CHAR(1);

/*==============================================================*/
/* Domaine : STREET_PREFIX                                      */
/*==============================================================*/
create domain STREET_PREFIX as VARCHAR(3);

/*==============================================================*/
/* Table : ADDRESS                                              */
/*==============================================================*/
create table ADDRESS (
   ADDRESS_ID           INT4                 not null,
   ZIP_CODE             VARCHAR(10)          not null,
   CITY_NAME            VARCHAR(80)          not null,
   STREET_NAME          VARCHAR(128)         not null,
   STREET_NUMBER        NUMERIC(4,0)         not null,
   COUNTRY_NAME         VARCHAR(80)          not null,
   STREET_PREFIX        VARCHAR(3)           null
      constraint CKC_STREET_PREFIX_ADDRESS check (STREET_PREFIX is null or (STREET_PREFIX in ('bis','ter'))),
   BUILDING_NUMBER      NUMERIC(4,0)         null,
   APT_NUMBER           NUMERIC(4,0)         null,
   constraint PK_ADDRESS primary key (ADDRESS_ID)
);

/*==============================================================*/
/* Table : CONNECTION                                           */
/*==============================================================*/
create table CONNECTION (
   CLIENT_IP            VARCHAR(15)          not null,
   USER_ID              VARCHAR(64)          not null,
   SESSION_ID           VARCHAR(64)          not null,
   LAST_ACCESS          DATE                 not null default CURRENT_TIMESTAMP,
   LIFE_TIME            INT4                 not null,
   LINK_PATH            VARCHAR(260)         null,
   constraint PK_CONNECTION primary key (CLIENT_IP)
);

/*==============================================================*/
/* Table : IDENTITY                                             */
/*==============================================================*/
create table IDENTITY (
   IDENTITY_ID          INT4                 not null,
   ADDRESS_ID           INT4                 null,
   FIRST_NAME           VARCHAR(80)          not null,
   LAST_NAME            VARCHAR(80)          not null,
   BIRTH_DAY            DATE                 not null,
   SEX                  CHAR(1)              not null
      constraint CKC_SEX_IDENTITY check (SEX in ('M','F')),
   constraint PK_IDENTITY primary key (IDENTITY_ID)
);

/*==============================================================*/
/* Table : SESSION                                              */
/*==============================================================*/
create table SESSION (
   SESSION_ID           VARCHAR(64)          not null,
   LOCAL_PATH           VARCHAR(260)         not null,
   constraint PK_SESSION primary key (SESSION_ID)
);

/*==============================================================*/
/* Table : "USER"                                               */
/*==============================================================*/
create table "USER" (
   USER_ID              VARCHAR(64)          not null,
   IDENTITY_ID          INT4                 null,
   USER_PWD             VARCHAR(64)          not null,
   CLIENT_ID            VARCHAR(64)          not null,
   USER_MAIL            VARCHAR(80)          not null,
   constraint PK_USER primary key (USER_ID)
);

alter table CONNECTION
   add constraint FK_CONNECTI_ATTACHER__SESSION foreign key (SESSION_ID)
      references SESSION (SESSION_ID)
      on delete restrict on update restrict;

alter table CONNECTION
   add constraint FK_CONNECTI_OUVRIR_CI_USER foreign key (USER_ID)
      references "USER" (USER_ID)
      on delete restrict on update restrict;

alter table IDENTITY
   add constraint FK_IDENTITY_POSSEDER__ADDRESS foreign key (ADDRESS_ID)
      references ADDRESS (ADDRESS_ID)
      on delete restrict on update restrict;

alter table "USER"
   add constraint FK_USER_RENSEIGNE_IDENTITY foreign key (IDENTITY_ID)
      references IDENTITY (IDENTITY_ID)
      on delete restrict on update restrict;
