.PHONY: test watch phpstan

test:
	./vendor/bin/phpunit tests --colors

watch:
	./vendor/bin/phpunit-watcher watch tests --colors

phpstan:
	./vendor/bin/phpstan analyse src --level 7
