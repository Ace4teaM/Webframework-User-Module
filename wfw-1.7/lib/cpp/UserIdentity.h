/***********************************************************************
 * Module:  UserIdentity.h
 * Author:  developpement
 * Modified: samedi 22 décembre 2012 08:10:43
 * Purpose: Declaration of the class UserIdentity
 ***********************************************************************/

#if !defined(__USER_UserIdentity_h)
#define __USER_UserIdentity_h

class UserAccount;
class UserAddress;

class UserIdentity
{
public:
   int userIdentityId;
   std::string firstName;
   std::string lastName;
   Date birthDay;
   std::string sex;
   
   UserAccount** userAccount;
   UserAddress* userAddress;

protected:
private:

};

#endif