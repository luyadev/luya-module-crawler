# CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](https://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 3.7.2 (31. October 2023)

+ [#55](https://github.com/luyadev/luya-module-crawler/pull/55) Added indonesia language

## 3.7.1 (10. Mai 2023)

+ Changed max char length for builder and index tables `content` field to 16,777,215. According to migrations even 4,294,967,295 characters would be supported.

## 3.7.0 (18. January 2023)

+ New `$encode` option for crawl command. If linkcheck is true, the links will be added to a list. Control whether adding the link to the list should encode or not.

## 3.6.0 (5. October 2022)

+ [#48](https://github.com/luyadev/luya-module-crawler/pull/48) Added events `beforeProcess` and `afterIndex` in order to interact with search results from a none crawled source.

## 3.5.0 (28. April 2022)

+ [#46](https://github.com/luyadev/luya-module-crawler/pull/46) Prevent the crawler from purge the full index when the builder index is empty. This can be disabled with the new option `--purging=1`.

## 3.4.1 (28. April 2022)

+ [#45](https://github.com/luyadev/luya-module-crawler/pull/45) Use transaction to sync index table when crawler finish the process.

## 3.4.0 (5. April 2022)

+ Updated deps to latest version of `smalot/pdfparser` parser which now requires at least version php 7.1. Therefore raise php version requirements for luya module crawler to version 7.1 to (which is outdated for a long time already: https://www.php.net/supported-versions.php)

## 3.3.1 (9. December 2021)

+ Small changes in docs, translations, composer dependencies

## 3.3.0 (10. August 2021)

+ [#40](https://github.com/luyadev/luya-module-crawler/pull/40) Add keywords to content string in order to make them searchable.

## 3.2.4 (15. April 2021)

+ Adjusted the default url rule for the crawler, the action was missing before `crawler/default` now `crawler/default/index`.

## 3.2.3 (25. March 2021)

+ [#39](https://github.com/luyadev/luya-module-crawler/pull/39) Added Bulgarian translations

## 3.2.2 (24. March 2021)

+ Added default views for the crawler index action

## 3.2.1 (13. January 2021)

+ [#38](https://github.com/luyadev/luya-module-crawler/pull/38) Added max length validator for content in order to fix mysql error `SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'content' at row 1`.

## 3.2.0 (22. December 2020)

+ [#37](https://github.com/luyadev/luya-module-crawler/pull/37) Added link check support for relative paths on the website. Use head method for link check instead of get and follow those links if needed. Added PHP 8 tests.

## 3.1.0 (12. November 2020)

+ [#36](https://github.com/luyadev/luya-module-crawler/pull/36) Add concurrent requests configuration option for crawl command.

## 3.0.0 (21. October 2020)

> This release contains new migrations and requires to run the migrate command after updating. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

+ Crawl mechanism refactoring using https://github.com/nadar/crawler. 
+ Dropped unused module properties and crawler classes, see [Upgrade](UPGRADE.md)
+ Indexing of PDFs is now by default activated.

## 2.0.5 (8. April 2020)

+ [#29](https://github.com/luyadev/luya-module-crawler/pull/29) Improve performance, create new indexes, improve when working with group conditions.

## 2.0.4 (5. January 2020)

+ [#28](https://github.com/luyadev/luya-module-crawler/pull/28) Ensure levenshtein input string does not exceed 255 chars.

## 2.0.3 (5. December 2019)

+ [#26](https://github.com/luyadev/luya-module-crawler/pull/26) Improve handling with lot of data, add more verbosity, add unit tests.

## 2.0.2 (22. October 2019)

+ New FR translations
+ New PT translations

## 2.0.1 (17. June 2019)

+ [#23](https://github.com/luyadev/luya-module-crawler/issues/23) Changed did you mean behavior with empty input values.

## 2.0.0 (29. May 2019)

+ Added new statistiscs overview
+ [#14](https://github.com/luyadev/luya-module-crawler/issues/14) Add relation between suggestions and search results.
+ [#1](https://github.com/luyadev/luya-module-crawler/issues/1) Add indexer interface with property to provide class which implement the interface.
+ [#20](https://github.com/luyadev/luya-module-crawler/issues/20) Added new link status list.

## 1.0.6.2 (4. March 2019)

+ [#19](https://github.com/luyadev/luya-module-crawler/issues/19) Fixed bug when regex delimiter is used in search keyword.

## 1.0.6.1 (11. February 2019)

+ [#17](https://github.com/luyadev/luya-module-crawler/issues/17) PHP warning is thrown in PHP 7.2 envs when using empty search.

## 1.0.6 (21. January 2019)

+ [#15](https://github.com/luyadev/luya-module-crawler/issues/15) Added dashboard object with latest keywords without results.
+ Added some missing translation keys.

## 1.0.5.1 (19. November 2018)

+ [#12](https://github.com/luyadev/luya-module-crawler/issues/12) Fixed bug with ending whitespace.

## 1.0.5 (17. November 2018)

+ [#11](https://github.com/luyadev/luya-module-crawler/issues/11) Switched to from htmlentities to htmlspecialchars for content crawling.
+ [#10](https://github.com/luyadev/luya-module-crawler/issues/10) Improved the order of pages with a new relevance to query score.
+ [#3](https://github.com/luyadev/luya-module-crawler/issues/3) Added new did you mean widget which returns suggestions based on search history.

## 1.0.4 (30. October 2018)

+ [#9](https://github.com/luyadev/luya-module-crawler/issues/9) Fix bug with double encoding of preview content.

## 1.0.3 (8. October 2018)

+ [#8](https://github.com/luyadev/luya-module-crawler/issues/8) Fix issue with utf8 chars for result previews.

## 1.0.2 (27. April 2018)

+ [#5](https://github.com/luyadev/luya-module-crawler/issues/5) Add option to provide group search in default controller.
+ [#4](https://github.com/luyadev/luya-module-crawler/issues/4) Add info when base url does not return status code 200.

## 1.0.1 (28. March 2018)

+ [#2](https://github.com/luyadev/luya-module-crawler/issues/2) Add database index keys for builder and index table.
+ Use LUYA Testsuite for unit tests.
+ Added PHPDocs.
+ Added Table output summary when crawler finish.

## 1.0.0 (12. December 2017)

+ First stable release
