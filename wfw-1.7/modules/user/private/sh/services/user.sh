#!/bin/sh
echo "[(C)2012-ID-Informatik] Configure module: user..."
#
# configure le module 'user'
# Usage: ./user.add.sh [web_base_dir] [cp_att]
#

#
# arguments ?
#
if [ $# -lt 2 ]
  then 
  echo "Usage:\n\t./user.sh [service] [web_base_dir] [cp_att]"
  exit 2002
fi

#
# GLOBALES
#
. ../path.inc 2>&1 # include avec redirection StdErr vers StdOutput pour voir les erreurs en ligne de commande php
if [ wfw_dir = "" ]
  then
  echo "unknown wfw path"
  exit
fi

#
# SERVICE
#
case "$1" in
  configure)
    # configure les fichiers "default.xml"
    echo "\tconfigure..."
    php "$wfw_dir/req/add_module.php" site_path="$www_dir" module="user" > /dev/null 2>&1

    # Configure la base de données
    echo "\tconfigure la base de données..."
    psql -f $wfw_dir/modules/user/private/sql/user/create.sql
    psql -f $wfw_dir/modules/user/private/sql/user/tables.sql
    psql -f $wfw_dir/modules/user/private/sql/user/init.sql
    psql -f $wfw_dir/modules/user/private/sql/user/func.sql

    exit 0
    ;;
  remove)
    # ferme les sessions ouvertes
    echo "\tlogoout session..."
    php "$www_dir/private/req/user/all_logout.php" > /dev/null 2>&1

    # configure les fichiers "default.xml"
    echo "\tunconfigure..."
    php "$wfw_dir/req/rem_module.php" site_path="$www_dir" module="user" > /dev/null 2>&1

    # Configure la base de données
    echo "\tconfigure la base de données..."
    psql -f $www_dir/private/sql/user/remove.sql

    exit 0
    ;;
  save)
    echo "\tsaving data not implemented..."
    #exporte la base de données
    ;;
  restore)
    echo "\trestoring data not implemented..."
    #importe la base de données
    #recrer les liens des utilisateurs
    #connect les sessions actives
    ;;
  *)
  ;;
esac


exit 0
