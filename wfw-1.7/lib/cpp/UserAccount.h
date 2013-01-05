/***********************************************************************
 * Module:  UserAccount.h
 * Author:  developpement
 * Modified: jeudi 20 décembre 2012 18:21:38
 * Purpose: Declaration of the class UserAccount
 ***********************************************************************/

#if !defined(__USER_UserAccount_h)
#define __USER_UserAccount_h

class UserConnection;
class UserIdentity;

class UserAccount
{
public:
   std::string userAccountId;
   std::string userPwd;
   std::string clientId;
   std::string userMail;
   
   UserConnection** userConnection;
   UserIdentity* userIdentity;

protected:
private:

};

#endif