/***********************************************************************
 * Module:  UserSession.h
 * Author:  developpement
 * Modified: samedi 22 décembre 2012 07:34:37
 * Purpose: Declaration of the class UserSession
 ***********************************************************************/

#if !defined(__USER_UserSession_h)
#define __USER_UserSession_h

class UserConnection;

class UserSession
{
public:
   std::string userSessionId;
   std::string localPath;
   
   UserConnection** userConnection;

protected:
private:

};

#endif