#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}


WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=/tmp/wordpress/
EXEC_DIR="$(pwd)"

set -ex

install_wp() {
	mkdir -p $WP_CORE_DIR

	local ARCHIVE_NAME="wordpress-$WP_VERSION"
	wget -nv -O /tmp/wordpress.tar.gz http://wordpress.org/${ARCHIVE_NAME}.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

	cp /tmp/ci_config/db.php $WP_CORE_DIR/wp-content/db.php 
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite
	mkdir -p $WP_TESTS_DIR
	cd $WP_TESTS_DIR
	svn co --quiet http://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/includes/
	svn co --quiet http://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/tests/
	svn co --quiet http://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit/data/

	wget -nv -O wp-tests-config.php http://develop.svn.wordpress.org/tags/${WP_VERSION}/wp-tests-config-sample.php
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" wp-tests-config.php

	# modify the path to reflect actual test folder path
	sed $ioption "s:replace/:$WP_TESTS_DIR/tests/:" $EXEC_DIR/tests/phpunit.xml

}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# drop database if it exists (-f forces so no prompt to confirm, and ignores error if db didnt exist)
	#mysqladmin drop $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA -f
	mysql -u "$DB_USER" --password="$DB_PASS" -e "drop database if exists $DB_NAME"$EXTRA

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_code_sniffer() {
	# Install CodeSniffer for WordPress Coding Standards checks.
	git clone --quiet https://github.com/squizlabs/PHP_CodeSniffer.git /tmp/php-codesniffer

	# Install WordPress Coding Standards.
	git clone --quiet https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git /tmp/wordpress-coding-standards

	# Hop into CodeSniffer directory.
	cd /tmp/php-codesniffer

	# Set install path for WordPress Coding Standards
	# @link https://github.com/squizlabs/PHP_CodeSniffer/blob/4237c2fc98cc838730b76ee9cee316f99286a2a7/CodeSniffer.php#L1941
	./scripts/phpcs --config-set installed_paths ../wordpress-coding-standards

	# Return to build directory and rehash env vars
	cd $EXEC_DIR
	phpenv rehash
}

install_lints() {
	# First we copy the package.json from /tmp/ci_config into the $EXEC_DIR
	cp /tmp/ci_config/package.json $EXEC_DIR

	# Install dependencies
	npm install

	# Copy the CSS Lint Tool (csslint) config
	cp /tmp/ci_config/.csslintrc $EXEC_DIR

	# Copy the javascript Lint Tool (eslint) config
	cp /tmp/ci_config/.eslintrc $EXEC_DIR
}

update_postmedia_test_config() {
	# pull down the custom files required to support wordpress and the testing configs
	cd $EXEC_DIR
	git clone --quiet https://github.com/Postmedia-Digital/CI_Config.git /tmp/ci_config

	# copy the codesniffer ruleset into the tests folder.
	cp /tmp/ci_config/codesniffer.ruleset.xml $EXEC_DIR/tests/

	# copy the phpunit test config into the tests folder.
	cp /tmp/ci_config/phpunit.xml $EXEC_DIR/tests/

	if [ $WP_VERSION == 'latest' ]; then 
		#allows us to set the standard on latest from the CI_Config repo
		#this was needed because trunk is the nightly build, no easy way to id latest stable
		WP_VERSION="$(/tmp/ci_config/wordpress_latest.sh)"
	fi
}

remove_previous_temp_files() {
	# this function removes and reverts files before the installations, no errors are passed on if they don't exist
	rm -rf /tmp/wordpress*
	rm -rf /tmp/ci_config
	rm -rf /tmp/php-codesniffer
	git checkout $EXEC_DIR/tests/phpunit.xml
	rm -f $EXEC_DIR/tests/phpunit.xml.bak
}

remove_previous_temp_files
update_postmedia_test_config
install_wp
install_test_suite
install_db
install_code_sniffer
install_lints

