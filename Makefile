# This file is licensed under the Affero General Public License version 3 or
# later. See the COPYING file.
app_name=$(notdir $(CURDIR))
build_tools_directory=$(CURDIR)/build/tools
composer=$(shell which composer 2> /dev/null)

project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
sign_dir=$(build_dir)/sign
package_name=$(app_name)
cert_dir=$(HOME)/Projekte/ShiftsApp/openssl
version = 1.9.0

all: dev-setup lint build-js-production test

# Dev env management
dev-setup: clean clean-dev composer npm-init


# Installs and updates the composer dependencies. If composer is not installed
# a copy is fetched from the web
composer:
ifeq (, $(composer))
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)
	php $(build_tools_directory)/composer.phar install --prefer-dist
	php $(build_tools_directory)/composer.phar update --prefer-dist
else
	composer install --prefer-dist
	composer update --prefer-dist
endif

all: dev-setup build-js-production

dev-setup: clean-dev npm-init

dependabot: dev-setup npm-update build-js-production

release: appstore

release-tag: appstore create-tag

build-js:
	npm --openssl-legacy-provider run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

test:
	npm run test:unit

lint:
	npm run lint

lint-fix:
	npm run lint:fix

npm-init:
	npm ci

npm-update:
	npm update

clean:
	rm -rf js/*
	rm -rf $(build_dir)

clean-dev: clean
	rm -rf node_modules

create-tag:
	git tag -a v$(version) -m "Tagging the $(version) release."
	git push origin v$(version)

# Tests
test:
	./vendor/phpunit/phpunit/phpunit -c phpunit.xml
	./vendor/phpunit/phpunit/phpunit -c phpunit.integration.xml

appstore:
	rm -rf $(build_dir)
	mkdir -p $(sign_dir)
	mkdir -p $(cert_dir)
	rsync -a \
	--exclude=babel.config.js \
	--exclude=/build \
	--exclude=composer.json \
	--exclude=composer.lock \
	--exclude=docs \
    --exclude=.idea \
	--exclude=.drone.yml \
	--exclude=.eslintignore \
	--exclude=.eslintrc.js \
	--exclude=.git \
	--exclude=.gitattributes \
	--exclude=.github \
	--exclude=.gitignore \
	--exclude=jest.config.js \
	--exclude=.l10nignore \
	--exclude=mkdocs.yml \
	--exclude=Makefile \
	--exclude=node_modules \
	--exclude=package.json \
	--exclude=package-lock.json \
	--exclude=.php_cs.dist \
	--exclude=.php_cs.cache \
	--exclude=README.md \
	--exclude=src \
	--exclude=.stylelintignore \
	--exclude=stylelint.config.js \
	--exclude=.tx \
	--exclude=tests \
	--exclude=vendor \
	--exclude=webpack.*.js \
	$(project_dir)/  $(sign_dir)/$(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing app files…"; \
        docker exec master_nextcloud_1 adduser --disabled-password --gecos "" builder; \
		docker exec -u builder master_nextcloud_1 php /var/www/html/occ integrity:sign-app \
			--privateKey=/$(app_name).key\
			--certificate=/$(app_name).crt\
			--path=/var/www/html/apps-extra/$(app_name); \
	fi
	tar -czf $(build_dir)/$(app_name)-$(version).tar.gz \
		-C $(sign_dir) $(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing package…"; \
		openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(build_dir)/$(app_name)-$(version).tar.gz | openssl base64; \
	fi
