SHELL:=/bin/bash

help:
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[0;36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST) | sort

build:  ## Собрать контейнер
	@docker-compose build --no-cache

stop: ## Остановить все контейнеры
	@docker-compose down

start_web7: stop  ## Запустить контейнер
	@docker-compose up web7

start_web8: stop  ## Запустить контейнер
	@docker-compose up web8

update:  ## Установить/Обновить модуль
	@rm -rf php/wp-content/plugins/komtetkassa/* &&\
	 cp -r src/. php/wp-content/plugins/komtetkassa/

release:  ## Архивировать для загрузки в маркет
	@cd ./src &&\
	zip -r komtetkassa.zip includes komtetkassa.php uninstall.php &&\
	mv komtetkassa.zip ../

.PHONY: help
.DEFAULT_GOAL := help
