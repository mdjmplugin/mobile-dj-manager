#
# Assets
#
build-css:
	./node_modules/.bin/grunt cssmin

build-js:
	./node_modules/.bin/grunt uglify

#
# Linting
#
lint: lint-wordpress lint-wordpress-source

lint-wordpress:
	./vendor/bin/phpcs > phpcs_all.txt

lint-wordpress-source:
	./vendor/bin/phpcs --report=source > phpcs_source.txt

lint-wordpress-fix:
	./vendor/bin/phpcbf
