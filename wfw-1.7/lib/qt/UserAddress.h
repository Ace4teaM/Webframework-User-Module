/***********************************************************************
 * Module:  UserAddress.h
 * Author:  developpement
 * Modified: lundi 31 décembre 2012 16:56:49
 * Purpose: Declaration of the class UserAddress
 ***********************************************************************/

#if !defined(__USER_UserAddress_h)
#define __USER_UserAddress_h

class UserIdentity;

class UserAddress
{
public:
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