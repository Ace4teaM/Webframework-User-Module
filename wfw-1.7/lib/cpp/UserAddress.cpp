/***********************************************************************
 * Module:  UserAddress.cpp
 * Author:  developpement
 * Modified: samedi 22 décembre 2012 08:35:50
 * Purpose: Implementation of the class UserAddress
 ***********************************************************************/

#include "UserIdentity.h"
#include "UserAddress.h"

////////////////////////////////////////////////////////////////////////
// Name:       UserAddress::getIdentity()
// Purpose:    Implementation of UserAddress::getIdentity()
// Return:     UserIdentity
////////////////////////////////////////////////////////////////////////

UserIdentity UserAddress::getIdentity(void)
//obtient la base de donnees courrante
global $app;
if(!$app->getDB($db))
 return false;

//execute la requete
$query = "SELECT * from user_identity inner join user_address a on id = $this->addressId where id = a.identity_id";

return IdentityMgr::bound($query);