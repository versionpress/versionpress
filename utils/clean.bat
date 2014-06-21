REM Deletes VersionPress working files in case you want to start over. Run from the WP site root.

rmdir ".git" /s /q
rmdir "wp-content\plugins\versionpress\db" /s /q
del "wp-content\plugins\versionpress\.active" /q