.PHONY: test watch phpstan docker_build docker_test docker_phpstan

test:
	./vendor/bin/phpunit tests --colors --coverage-text --whitelist=src

watch:
	./vendor/bin/phpunit-watcher watch tests --colors

phpstan:
	./vendor/bin/phpstan analyse src --level 7

docker_build:
	docker build --build-arg PHP_VERSION=7.1 -t isocontent:7.1 .
	docker build --build-arg PHP_VERSION=7.1 --build-arg COMPOSER_FLAGS="--prefer-lowest" -t isocontent:7.1-lowdeps .
	docker build --build-arg PHP_VERSION=7.2 -t isocontent:7.2 .
	docker build --build-arg PHP_VERSION=7.2 --build-arg COMPOSER_FLAGS="--prefer-lowest" -t isocontent:7.2-lowdeps .
	docker build --build-arg PHP_VERSION=7.3 -t isocontent:7.3 .
	docker build --build-arg PHP_VERSION=7.3 --build-arg COMPOSER_FLAGS="--prefer-lowest" -t isocontent:7.3-lowdeps .

docker_test: docker_build
	docker run -it --rm isocontent:7.1 ./vendor/bin/phpunit tests --colors
	docker run -it --rm isocontent:7.1-lowdeps ./vendor/bin/phpunit tests --colors
	docker run -it --rm isocontent:7.2 ./vendor/bin/phpunit tests --colors
	docker run -it --rm isocontent:7.2-lowdeps ./vendor/bin/phpunit tests --colors
	docker run -it --rm isocontent:7.3 ./vendor/bin/phpunit tests --colors
	docker run -it --rm isocontent:7.3-lowdeps ./vendor/bin/phpunit tests --colors

docker_phpstan: docker_build
	docker run -it --rm isocontent:7.1 ./vendor/bin/phpstan analyse src --level 7
	docker run -it --rm isocontent:7.1-lowdeps ./vendor/bin/phpstan analyse src --level 7
	docker run -it --rm isocontent:7.2 ./vendor/bin/phpstan analyse src --level 7
	docker run -it --rm isocontent:7.2-lowdeps ./vendor/bin/phpstan analyse src --level 7
	docker run -it --rm isocontent:7.3 ./vendor/bin/phpstan analyse src --level 7
	docker run -it --rm isocontent:7.3-lowdeps ./vendor/bin/phpstan analyse src --level 7

