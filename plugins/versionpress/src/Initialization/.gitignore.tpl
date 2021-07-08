#------------------------
#  Main ignored items
#------------------------

{{abspath-parent}}/wp-config.php
{{abspath}}/wp-config.php
.maintenance
versionpress.maintenance
/.htaccess
/web.config
.well-known/

{{wp-content}}/*
!{{wp-content}}/db.php
!{{wp-content}}/index.php
!{{wp-plugins}}/
{{wp-plugins}}/versionpress/
!{{wp-content}}/.versionpress/
!{{wp-content}}/mu-plugins/
!{{wp-content}}/themes/
!{{wp-content}}/languages/
!{{wp-content}}/uploads/
!{{wp-content}}/vpdb/


#------------------------
#  Cache files
#------------------------

{{wp-content}}/uploads/cache
{{wp-content}}/uploads/et_temp


#------------------------
#  Log files
#------------------------

*.log
error_log
access_log


#------------------------
#  OS files
#------------------------

.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
*[Tt]humbs.db
*.Trashes


#------------------------
#  IDE files
#------------------------

.idea/
.vscode/
