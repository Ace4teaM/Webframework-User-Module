/***********************************************************************
 * Module:  UserConnection.h
 * Author:  developpement
 * Modified: jeudi 20 décembre 2012 18:17:17
 * Purpose: Declaration of the class UserConnection
 ***********************************************************************/

#if !defined(__USER_UserConnection_h)
#define __USER_UserConnection_h

class UserAccount;
class UserSession;

class UserConnection
{
public:
   int userConnectionId;
   std::string clientIp;
   Date lastAccess;
   int lifeTime;
   std::string linkPath;
   
   UserAccount* userAccount;
   UserSession* userSession;

protected:
private:

};

#endif