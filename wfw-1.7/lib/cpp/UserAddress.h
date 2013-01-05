/***********************************************************************
 * Module:  UserAddress.h
 * Author:  developpement
 * Modified: samedi 22 décembre 2012 08:35:50
 * Purpose: Declaration of the class UserAddress
 ***********************************************************************/

#if !defined(__USER_UserAddress_h)
#define __USER_UserAddress_h

class UserIdentity;

#include <UserIdentity.h>

class UserAddress
{
public:
   UserIdentity getIdentity(void);

   int userAddressId;
   std::string zipCode;
   std::string cityName;
   std::string streetName;
   double streetNumber;
   std::string countryName;
   std::string streetPrefix;
   double buildingNumber;
   double aptNumber;
   
   UserIdentity** userIdentity;

protected:
private:

};

#endif