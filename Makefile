#!make

ML_VERSION = latest

.PHONY: help dev-init verify-hooks test lint lint-markdown fix fix-markdown code-analyse check

help:
	@echo
	@echo "Hooks"
	@echo "--------------------------------------------------------------------------------"
	@echo "  dev-init             Register git hooks (core.hooksPath)"
	@echo "  verify-hooks         Verify git hooks (core.hooksPath)"
	@echo
	@echo "Quality"
	@echo "--------------------------------------------------------------------------------"
	@echo "  lint                 composer code-style + code-analyse + lint-markdown"
	@echo "  lint-markdown        markdownlint-cli2 (Docker, read-only)"
	@echo "  code-analyse         composer code-analyse (PHPStan)"
	@echo "  check                composer check (format + style + analyse + tests)"
	@echo "  fix                  composer code-format + fix-markdown"
	@echo "  fix-markdown         Prettier + markdownlint --fix (Docker)"
	@echo "  test                 composer tests (PHPUnit)"
	@echo

dev-init:
	@vendor/bin/kj-php-coding-standard-install-hooks

verify-hooks:
	@bash vendor/kristijorgji/php-coding-standard/scripts/check-hooks.sh

test:
	XDEBUG_MODE=off composer tests

code-analyse:
	XDEBUG_MODE=off composer code-analyse

check:
	XDEBUG_MODE=off composer check

lint:
	XDEBUG_MODE=off composer code-style
	XDEBUG_MODE=off composer code-analyse
	@$(MAKE) --no-print-directory lint-markdown

lint-markdown:
	@echo "################################################################################"
	@echo "# markdownlint-cli2"
	@echo "################################################################################"
	@docker run --rm -v $(PWD):/data -w /data davidanson/markdownlint-cli2:$(ML_VERSION) "**/*.md"

fix:
	XDEBUG_MODE=off composer code-format
	@$(MAKE) --no-print-directory fix-markdown

fix-markdown:
	@echo "################################################################################"
	@echo "# Prettier (Restricted to Markdown)"
	@echo "################################################################################"
	@docker run --rm \
		-v $(PWD):/work \
		-w /work \
		--user $$(id -u):$$(id -g) \
		tmknom/prettier:latest \
		--write "**/*.md" \
		--parser markdown \
		--ignore-path .gitignore
	@echo "################################################################################"
	@echo "# markdownlint-cli2 --fix"
	@echo "################################################################################"
	@docker run --rm -v $(PWD):/data -w /data davidanson/markdownlint-cli2:$(ML_VERSION) --fix "**/*.md"
