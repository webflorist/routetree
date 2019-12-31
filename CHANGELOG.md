# Release Notes

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