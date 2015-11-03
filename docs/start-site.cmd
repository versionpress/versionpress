:: Starts a docs site. Used from 'gulp start-site' task.
::
:: See http://stackoverflow.com/a/28692723/21728
:: Note that IIS express doesn't return so it has to be start'd, see http://stackoverflow.com/a/28213008/21728

@echo off

call "C:\Program Files (x86)\Microsoft Visual Studio 14.0\Common7\Tools\VsDevCmd.bat"

echo.
echo === Starting build ===
echo.

msbuild ..\VersionPress-docssite\VersionPress.DocsSite.sln



echo.
echo === Starting IIS Express ===
echo.

:: First, find the absolute path of DocsSite, see http://stackoverflow.com/a/4488734/21728
pushd ..\VersionPress-docssite\VersionPress.DocsSite
set DOCS_SITE_PATH=%CD%
popd

echo Relative path: %REL_PATH%
echo Maps to path: %ABS_PATH%

start "" "C:\Program Files (x86)\IIS Express\iisexpress" /path:%DOCS_SITE_PATH% /port:1515