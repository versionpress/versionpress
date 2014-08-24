set destination_dir=..\VersionPress-docssite\VersionPress.DocsSite\App_Data\content

rmdir "%destination_dir%" /s /q
xcopy content "%destination_dir%" /Y /C /S /I
type NUL > "%destination_dir%\.changed"