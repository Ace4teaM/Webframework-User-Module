set dir=%~f0\..
set script=disconnect_user.php

cd %dir%\..

echo. >> log\tasks.txt
echo %DATE% %TIME% >> log\tasks.txt

php %script% output=xarg uid=%1 >> log\tasks.txt

echo. >> log\tasks.txt
echo %script% (%ERRORLEVEL%) >> log\tasks.txt

rem pause
rem exit %ERRORLEVEL%