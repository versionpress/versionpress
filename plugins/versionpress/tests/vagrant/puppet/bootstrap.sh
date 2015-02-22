#!/usr/bin/env bash

apt-get update --fix-missing

mkdir -p /etc/suphp
cp /vagrant/puppet/files/suphp.conf /etc/suphp/suphp.conf

if [ ! -d /etc/puppet/modules/apt ]; then
	puppet module install puppetlabs/apt;
fi

if [ ! -d /etc/puppet/modules/apache ]; then
	puppet module install puppetlabs/apache;
fi

if [ ! -d /etc/puppet/modules/mysql ]; then
	puppet module install puppetlabs/mysql;
fi

if [ ! -f /usr/local/bin/phpunit ]; then
	curl --silent --show-error --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-4.4.2.phar
fi

chmod +x /usr/local/bin/phpunit
