SHELL := /bin/bash
.PHONY: help install test lint lint-fix clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install

test: ## Run tests
	vendor/bin/phpunit

test-coverage: ## Run tests with coverage report
	vendor/bin/phpunit --coverage-html coverage

lint: ## Run code linter
	vendor/bin/phpcs

lint-fix: ## Fix code style issues
	vendor/bin/phpcbf

clean: ## Clean generated files
	rm -rf vendor/
	rm -rf coverage/
	rm -f composer.lock
	rm -f .phpunit.result.cache
