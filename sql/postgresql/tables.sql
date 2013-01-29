/*==============================================================*/
/* Nom de SGBD :  PostgreSQL 8                                  */
/* Date de création :  29/01/2013 15:39:11                      */
/*==============================================================*/


drop table USER_ACCOUNT;

drop table USER_ADDRESS;

drop table USER_CONNECTION;

drop table USER_IDENTITY;

drop table USER_REGISTRATION;

drop table USER_SESSION;

drop domain SEX;

drop domain STREET_PREFIX;

/*==============================================================*/
/* Domaine : SEX                                                */
/*==============================================================*/
create domain SEX as CHAR(1);

/*==============================================================*/
/* Domaine : STREET_PREFIX                                      */
/*==============================================================*/
create domain STREET_PREFIX as VARCHAR(3);

/*==============================================================*/
/* Table : USER_ACCOUNT                                         */
/*==============================================================*/
create table USER_ACCOUNT (
   USER_ACCOUNT_ID      VARCHAR(64)          not null,
   USER_IDENTITY_ID     INT4                 null,
   CLIENT_ID            VARCHAR(64)          not null,
   USER_MAIL            VARCHAR(80)          not null,
   USER_PWD             VARCHAR(64)          not null,
   constraint PK_USER_ACCOUNT primary key (USER_ACCOUNT_ID)
);

/*==============================================================*/
/* Table : USER_ADDRESS                                         */
/*==============================================================*/
create table USER_ADDRESS (
   USER_ADDRESS_ID      INT4                 not null,
   ZIP_CODE             VARCHAR(10)          not null,
   CITY_NAME            VARCHAR(80)          not null,
   STREET_NAME          VARCHAR(128)         not null,
   STREET_NUMBER        NUMERIC(4,0)         not null,
   COUNTRY_NAME         VARCHAR(80)          not null,
   STREET_PREFIX        VARCHAR(3)           null
      constraint CKC_STREET_PREFIX_USER_ADD check (STREET_PREFIX is null or (STREET_PREFIX in ('bis','ter'))),
   BUILDING_NUMBER      NUMERIC(4,0)         null,
   APT_NUMBER           NUMERIC(4,0)         null,
   constraint PK_USER_ADDRESS primary key (USER_ADDRESS_ID)
);

/*==============================================================*/
/* Table : USER_CONNECTION                                      */
/*==============================================================*/
create table USER_CONNECTION (
   USER_CONNECTION_ID   INT4                 not null,
   CLIENT_IP            VARCHAR(15)          not null,
   USER_ACCOUNT_ID      VARCHAR(64)          not null,
   USER_SESSION_ID      VARCHAR(64)          not null,
   LAST_ACCESS          DATE                 not null default CURRENT_TIMESTAMP,
   LIFE_TIME            INT4                 not null,
   LINK_PATH            VARCHAR(260)         null,
   constraint PK_USER_CONNECTION primary key (CLIENT_IP, USER_CONNECTION_ID)
);

/*==============================================================*/
/* Table : USER_IDENTITY                                        */
/*==============================================================*/
create table USER_IDENTITY (
   USER_IDENTITY_ID     INT4                 not null,
   USER_ADDRESS_ID      INT4                 null,
   FIRST_NAME           VARCHAR(80)          not null,
   LAST_NAME            VARCHAR(80)          not null,
   BIRTH_DAY            DATE                 not null,
   SEX                  CHAR(1)              not null
      constraint CKC_SEX_USER_IDE check (SEX in ('M','F')),
   constraint PK_USER_IDENTITY primary key (USER_IDENTITY_ID)
);

/*==============================================================*/
/* Table : USER_REGISTRATION                                    */
/*==============================================================*/
create table USER_REGISTRATION (
   USER_REGISTRATION_ID INT4                 not null,
   USER_TOKEN           VARCHAR(8)           not null,
   USER_MAIL            VARCHAR(80)          not null,
   USER_ID              VARCHAR(64)          not null,
   constraint PK_USER_REGISTRATION primary key (USER_REGISTRATION_ID)
);

/*==============================================================*/
/* Table : USER_SESSION                                         */
/*==============================================================*/
create table USER_SESSION (
   USER_SESSION_ID      VARCHAR(64)          not null,
   LOCAL_PATH           VARCHAR(260)         not null,
   constraint PK_USER_SESSION primary key (USER_SESSION_ID)
);

alter table USER_ACCOUNT
   add constraint FK_USER_ACC_RENSEIGNE_USER_IDE foreign key (USER_IDENTITY_ID)
      references USER_IDENTITY (USER_IDENTITY_ID)
      on delete restrict on update restrict;

alter table USER_CONNECTION
   add constraint FK_USER_CON_ATTACHER__USER_SES foreign key (USER_SESSION_ID)
      references USER_SESSION (USER_SESSION_ID)
      on delete restrict on update restrict;

alter table USER_CONNECTION
   add constraint FK_USER_CON_OUVRIR_CI_USER_ACC foreign key (USER_ACCOUNT_ID)
      references USER_ACCOUNT (USER_ACCOUNT_ID)
      on delete restrict on update restrict;

alter table USER_IDENTITY
   add constraint FK_USER_IDE_POSSEDER__USER_ADD foreign key (USER_ADDRESS_ID)
      references USER_ADDRESS (USER_ADDRESS_ID)
      on delete restrict on update restrict;

