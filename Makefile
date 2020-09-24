.PHONY: test watch phpstan docker_build docker_test docker_phpstan

test:
	./vendor/bin/phpunit tests --colors --coverage-text --whitelist=src --coverage-clover=build/coverage/clover.xml

watch:
	./vendor/bin/phpunit-watcher watch tests --colors .

phpstan:
	./vendor/bin/phpstan analyse src --level 7

docker_build:
	docker build --build-arg PHP_VERSION=7.4 -t isocontent:7.4 .
	docker build --build-arg PHP_VERSION=7.4 --build-arg COMPOSER_FLAGS="--prefer-lowest" -t isocontent:7.4-lowdeps .

docker_test: docker_build
	docker run -it --rm isocontent:7.4 ./vendor/bin/phpunit tests --colors
	docker run -it --rm isocontent:7.4-lowdeps ./vendor/bin/phpunit tests --colors

docker_phpstan: docker_build
	docker run -it --rm isocontent:7.4 ./vendor/bin/phpstan analyse src --level 7
	docker run -it --rm isocontent:7.4-lowdeps ./vendor/bin/phpstan analyse src --level 7

