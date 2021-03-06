COMPOSER=composer --no-interaction

vendor:
	$(COMPOSER) install

clean:
	rm -fr vendor/ node_modules/

putconfigs:
	cp -Rp dev/local-settings.php local-settings.php

phpcs: vendor
	./vendor/bin/phpcs --extensions=php --standard=dev/standard -s src/

phpmd: vendor
	./vendor/bin/phpmd src/ text dev/standard/phpmd.xml

codestyle: phpcs phpmd

dbmigrate:
	./vendor/bin/doctrine migrations:migrate --no-interaction