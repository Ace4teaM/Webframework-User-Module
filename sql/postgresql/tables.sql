/*==============================================================*/
/* DBMS name:      PostgreSQL 8 (WFW)                           */
/* Created on:     31/08/2014 21:29:45                          */
/*==============================================================*/


drop index  if exists RENSEIGNER_CIF_FK;

drop index  if exists USER_ACCOUNT_PK;

drop table if exists USER_ACCOUNT  CASCADE;

drop index  if exists USER_ADDRESS_PK;

drop table if exists USER_ADDRESS  CASCADE;

drop index  if exists ATTACHER_CIF_FK;

drop index  if exists OUVRIR_CIF_FK;

drop index  if exists USER_CONNECTION_PK;

drop table if exists USER_CONNECTION  CASCADE;

drop index  if exists POSSEDER_CIF_FK;

drop index  if exists USER_IDENTITY_PK;

drop table if exists USER_IDENTITY  CASCADE;

drop index  if exists USER_REGISTRATION_PK;

drop table if exists USER_REGISTRATION  CASCADE;

drop index  if exists USER_SESSION_PK;

drop table if exists USER_SESSION  CASCADE;

drop domain if exists SEX CASCADE;

drop domain if exists STREET_PREFIX CASCADE;

/*==============================================================*/
/* Domain: SEX                                                  */
/*==============================================================*/
create domain SEX as CHAR(1);

/*==============================================================*/
/* Domain: STREET_PREFIX                                        */
/*==============================================================*/
create domain STREET_PREFIX as VARCHAR(3);

/*==============================================================*/
/* Table: USER_ACCOUNT                                          */
/*==============================================================*/
create table USER_ACCOUNT (
   USER_ACCOUNT_ID      VARCHAR(64)          not null,
   USER_IDENTITY_ID     INT4                 null,
   CLIENT_ID            VARCHAR(64)          null,
   USER_MAIL            VARCHAR(80)          not null,
   USER_PWD             VARCHAR(64)          not null,
   constraint PK_USER_ACCOUNT primary key (USER_ACCOUNT_ID)
);

/*==============================================================*/
/* Index: USER_ACCOUNT_PK                                       */
/*==============================================================*/
create unique index USER_ACCOUNT_PK on USER_ACCOUNT (
USER_ACCOUNT_ID
);

/*==============================================================*/
/* Index: RENSEIGNER_CIF_FK                                     */
/*==============================================================*/
create  index RENSEIGNER_CIF_FK on USER_ACCOUNT (
USER_IDENTITY_ID
);

/*==============================================================*/
/* Table: USER_ADDRESS                                          */
/*==============================================================*/
create table USER_ADDRESS (
   USER_ADDRESS_ID      SERIAL               not null,
   ZIP_CODE             VARCHAR(10)          not null,
   CITY_NAME            VARCHAR(80)          not null,
   STREET_NAME          VARCHAR(128)         not null,
   STREET_NUMBER        NUMERIC(4,0)         not null,
   COUNTRY_NAME         VARCHAR(80)          not null,
   STREET_PREFIX        STREET_PREFIX        null,
   BUILDING_NUMBER      NUMERIC(4,0)         null,
   APT_NUMBER           NUMERIC(4,0)         null,
   constraint PK_USER_ADDRESS primary key (USER_ADDRESS_ID)
);

/*==============================================================*/
/* Index: USER_ADDRESS_PK                                       */
/*==============================================================*/
create unique index USER_ADDRESS_PK on USER_ADDRESS (
USER_ADDRESS_ID
);

/*==============================================================*/
/* Table: USER_CONNECTION                                       */
/*==============================================================*/
create table USER_CONNECTION (
   USER_CONNECTION_ID   CHAR(22)             not null,
   CLIENT_IP            VARCHAR(15)          not null,
   USER_ACCOUNT_ID      VARCHAR(64)          not null,
   USER_SESSION_ID      VARCHAR(64)          not null,
   LAST_ACCESS          TIMESTAMP            not null default CURRENT_TIMESTAMP,
   LIFE_TIME            INT4                 not null,
   LINK_PATH            VARCHAR(260)         null,
   constraint PK_USER_CONNECTION primary key (CLIENT_IP, USER_CONNECTION_ID)
);

/*==============================================================*/
/* Index: USER_CONNECTION_PK                                    */
/*==============================================================*/
create unique index USER_CONNECTION_PK on USER_CONNECTION (
CLIENT_IP,
USER_CONNECTION_ID
);

/*==============================================================*/
/* Index: OUVRIR_CIF_FK                                         */
/*==============================================================*/
create  index OUVRIR_CIF_FK on USER_CONNECTION (
USER_ACCOUNT_ID
);

/*==============================================================*/
/* Index: ATTACHER_CIF_FK                                       */
/*==============================================================*/
create  index ATTACHER_CIF_FK on USER_CONNECTION (
USER_SESSION_ID
);

/*==============================================================*/
/* Table: USER_IDENTITY                                         */
/*==============================================================*/
create table USER_IDENTITY (
   USER_IDENTITY_ID     SERIAL               not null,
   USER_ADDRESS_ID      INT4                 null,
   FIRST_NAME           VARCHAR(80)          not null,
   LAST_NAME            VARCHAR(80)          not null,
   BIRTH_DAY            DATE                 not null,
   SEX                  SEX                  not null,
   constraint PK_USER_IDENTITY primary key (USER_IDENTITY_ID)
);

/*==============================================================*/
/* Index: USER_IDENTITY_PK                                      */
/*==============================================================*/
create unique index USER_IDENTITY_PK on USER_IDENTITY (
USER_IDENTITY_ID
);

/*==============================================================*/
/* Index: POSSEDER_CIF_FK                                       */
/*==============================================================*/
create  index POSSEDER_CIF_FK on USER_IDENTITY (
USER_ADDRESS_ID
);

/*==============================================================*/
/* Table: USER_REGISTRATION                                     */
/*==============================================================*/
create table USER_REGISTRATION (
   USER_REGISTRATION_ID SERIAL               not null,
   USER_TOKEN           VARCHAR(8)           not null,
   USER_MAIL            VARCHAR(80)          not null,
   USER_ID              VARCHAR(64)          not null,
   constraint PK_USER_REGISTRATION primary key (USER_REGISTRATION_ID)
);

/*==============================================================*/
/* Index: USER_REGISTRATION_PK                                  */
/*==============================================================*/
create unique index USER_REGISTRATION_PK on USER_REGISTRATION (
USER_REGISTRATION_ID
);

/*==============================================================*/
/* Table: USER_SESSION                                          */
/*==============================================================*/
create table USER_SESSION (
   USER_SESSION_ID      VARCHAR(64)          not null,
   LOCAL_PATH           VARCHAR(260)         null,
   constraint PK_USER_SESSION primary key (USER_SESSION_ID)
);

/*==============================================================*/
/* Index: USER_SESSION_PK                                       */
/*==============================================================*/
create unique index USER_SESSION_PK on USER_SESSION (
USER_SESSION_ID
);

alter table USER_ACCOUNT
   add constraint FK_RENSEIGNER_CIF foreign key (USER_IDENTITY_ID)
      references USER_IDENTITY (USER_IDENTITY_ID)
      on delete restrict on update restrict;

alter table USER_CONNECTION
   add constraint FK_ATTACHER_CIF foreign key (USER_SESSION_ID)
      references USER_SESSION (USER_SESSION_ID)
      on delete restrict on update restrict;

alter table USER_CONNECTION
   add constraint FK_OUVRIR_CIF foreign key (USER_ACCOUNT_ID)
      references USER_ACCOUNT (USER_ACCOUNT_ID)
      on delete restrict on update restrict;

alter table USER_IDENTITY
   add constraint FK_POSSEDER_CIF foreign key (USER_ADDRESS_ID)
      references USER_ADDRESS (USER_ADDRESS_ID)
      on delete restrict on update restrict;

