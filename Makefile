.PHONY: test
test:
	@docker-compose -f docker/docker-compose.yml -f docker/docker-compose.test.yml -p ${PROJECT_NAME}_tests up --build --abort-on-container-exit --renew-anon-volumes --force-recreate

.PHONY: dev
dev:
	@docker-compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml -p sweefy up --build --abort-on-container-exit --renew-anon-volumes --force-recreate

.PHONY: enter
enter:
	$(eval ID := $(shell docker ps -q --filter "label=com.docker.compose.project=sweefy" --filter "label=com.docker.compose.service=app"))
	@if [ -z $(ID) ]; then echo >&2 "Container is missing. Did you run make dev first?" && exit 1; fi
	@docker exec -u application -it $(ID) bash
