test:
	./vendor/bin/phpunit tests --colors

watch:
	./vendor/bin/phpunit-watcher watch tests --colors
