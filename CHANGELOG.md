# Release Notes

## [v2.3.0 (2020-08-21)](https://github.com/webflorist/routetree/compare/v2.2.1...v2.3.0)
### Added
- Added config `sitemap.route` to enable a route delivering a dynamic XML-sitemap.
- Added config `no_prefix_locales` to disable locale-prefix for specific languages.

## [v2.2.1 (2020-03-04)](https://github.com/webflorist/routetree/compare/v2.2.0...v2.2.1)
### Added
- Add method `omitActionNameFromRouteName` to RouteAction to circumvent problems with legacy route names.

## [v2.2.0 (2020-03-04)](https://github.com/webflorist/routetree/compare/v2.1.0...v2.2.0)
### Added
- Add config `sitemap.excluded_middleware`. Routes using these middleware will be automatically excluded from the sitemap (default is 'auth').
- Add Travis-CI build status image to readme (thanks@msnwalt!).
### Changed
- Change `laravel/framework` version dependency to `>=5.5 <7.0.0` as RouteTree is not compatible with Laravel 7 at the moment.
- Add php 7.4 to .travis.yml (thanks@msnwalt!).
- Modify .travis.yml to test Laravel v6.* as well as v5.6.*.
- Add licence (MIT) and keywords to composer.json. 
### Fixed
- Fix problem with path-generation, when a redirect-node is defined before it's targeted node. Also adding regression test.

## [v2.1.0 (2020-03-02)](https://github.com/webflorist/routetree/compare/v2.0.3...v2.1.0)
### Added
- Add config `localization.translate_resource_suffixes` to disable auto-translation of resource-related path suffixes (/create and /edit).
### Fixed
- Fix lastmod, changefreq and priority fetching from model on sitemap generation.
### Changed
- De-deprecate helper function route_node_url().

## [v2.0.3 (2020-01-29)](https://github.com/webflorist/routetree/compare/v2.0.2...v2.0.3)
### Changed
- Require laravel/framework >=5.5 and orchestra/testbench >=3.5.

## [v2.0.2 (2020-01-02)](https://github.com/webflorist/routetree/compare/v2.0.1...v2.0.2)
### Fixed
- Fix language-detection via HTTP_ACCEPT_LANGUAGE header.

## [v2.0.1 (2019-12-31)](https://github.com/webflorist/routetree/compare/v2.0.0...v2.0.1)
### Fixed
- Fix url of root page of single language sites ending with // in Sitemap.

## [v2.0.0 (2019-12-11)](https://github.com/webflorist/routetree/compare/v1.1.0...v2.0.0)
### Added
- Add sitemap-generator
- Add events
- Add automatic locale-setting based on Browser
- Add caching
- Add REST-API
- Allow RouteNodes to be generated for specific locales only.
- Add LinkBuilder
### Changed
- Refactor RouteNodes generation-syntax

## [v1.1.0 (2019-09-23)](https://github.com/webflorist/routetree/compare/v1.0.2...v1.1.0)
### Added
- Add config `start_paths_with_locale` to disable locale
- Add parameter to create relative urls to various functions (e.g. `route_node_url()` helper function). Defaults to create absolute urls (previous standard-behaviour).
- Add skip parameter to middleware-config for routes to bypass inherited middleware (thanks to moxx!).
### Changed
- Fall back to `app.locale`, if `app.locales` is not set when determining configures languages.