# Changelog of inpsyde/wp-tests-starter

## main
* Introduces Changelog
* Use new default branch `main`
* Raise minimum PHP version to 7.4
* Behavioral changes
  * `WpTestsStarter::useConst()` does not define the constant immediately like `::defineConst()` did
* API changes
  * Remove `Common\SaltGeneratorInterface`
  * Rename `Common\*` to `Helper\*`
  * Introduce `Helper\DbUrlParser`
  * Rename mutator methods `WpTestsStarter::set*()` and `::define*()` to `::use*()`
  * Rename method `WpTestsStarter::defineConst()` to `::useConst()`
  * Add method `WpTestsStarter::addLivePlugin()`
  * Add methods `WpTestsStarter::addFilter()` and `::addAction()` to mimic `add_filter()` and `add_action()` before WP is loaded
* Internal refactoring
  * Move phpunit*.xml.dist to root directory
  * Reformat code
  * Add DDEV as development environment
  * Update dev dependencies

## 1.0.2
* Add `WpTestsStarter::getConfigFile()`
* Fix: write constants into test config file #3

## 1.0.1
* typo fixes
* License added

## 1.0.0
* initial release
