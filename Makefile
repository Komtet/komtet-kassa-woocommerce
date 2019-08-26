SHELL:=/bin/bash

help:
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[0;36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST) | sort

build:  ## Собрать контейнер
	@docker-compose build

stop: ## Остановить все контейнеры
	@docker-compose down

start_web5: stop  ## Запустить контейнер
	@docker-compose up -d web5

start_web7: stop  ## Запустить контейнер
	@docker-compose up -d web7

update:  ## Установить/Обновить модуль
	@rm -rf php/wp-content/plugins/komtetkassa/* &&\
	 cp -r src/. php/wp-content/plugins/komtetkassa/

release:  ## Архивировать для загрузки в маркет
	@cd ./src &&\
	zip -r komtetkassa.zip includes komtetkassa.php uninstall.php &&\
	mv komtetkassa.zip ../

.PHONY: help
.DEFAULT_GOAL := help
