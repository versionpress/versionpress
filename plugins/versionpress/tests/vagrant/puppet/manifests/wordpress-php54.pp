
class { 'apache': }

include apt

apt::source { 'wheezy_backports':
  location    => 'http://ftp.debian.org/debian',
  release     => 'wheezy-backports',
  repos       => 'main',
  include_src => false,
}

package { [
  'subversion',
  'git',
  'php5-cli',
  'php5-curl',
  'php5-gd',
  'php5-imagick',
  'php5-mcrypt',
  'php5-xdebug'
]: ensure => latest }

apt::force { 'nodejs-legacy':
  release => 'wheezy-backports',
  require => Class['apt']
}
exec { 'download-npm':
  command => '/usr/bin/curl --silent --show-error --output /tmp/npm_install.sh --location https://www.npmjs.com/install.sh',
  creates => '/tmp/npm_install.sh',
  require => Apt::Force['nodejs-legacy'],
}
exec { 'install-npm':
  command => '/bin/bash /tmp/npm_install.sh',
  cwd => '/tmp',
  environment => 'clean=no',
  creates => '/usr/bin/npm',
  require => Exec['download-npm']
}
exec { 'grunt-cli':
  command => '/usr/bin/npm install -g grunt-cli',
  creates => '/usr/bin/grunt',
  require => Exec['install-npm']
}

include apache::mod::suphp

apache::mod { 'rewrite': }

apache::vhost { 'wordpress':
  servername       => $::fqdn,
  port             => '80',
  docroot          => '/vagrant/wordpress/build',
  docroot_owner    => 'vagrant',
  docroot_group    => 'vagrant',
  suphp_addhandler => 'application/x-httpd-suphp',
  suphp_engine     => 'on',
  suphp_configpath => '/etc/php5/cgi',
  custom_fragment  => 'RewriteLogLevel 2
                       RewriteLog /var/log/apache2/rewrite.log'
}

apache::vhost { 'wordpress-ssl':
  servername       => $::fqdn,
  port             => '443',
  docroot          => '/vagrant/wordpress/build',
  docroot_owner    => 'vagrant',
  docroot_group    => 'vagrant',
  ssl              => true,
  suphp_addhandler => 'application/x-httpd-suphp',
  suphp_engine     => 'on',
  suphp_configpath => '/etc/php5/cgi',
  custom_fragment  => 'RewriteLogLevel 2
                       RewriteLog /var/log/apache2/rewrite-ssl.log'
}

class { 'mysql::server':
  root_password => 'wordpress'
}

class { 'mysql::bindings':
  php_enable => 'true',
}

mysql::db { ['wordpress', 'wordpress-tests']:
  ensure   => present,
  charset  => 'utf8',
  user     => 'wordpress',
  password => 'wordpress',
  host     => 'localhost',
  grant    => ['ALL'],
  require  => Class['mysql::server']
}
