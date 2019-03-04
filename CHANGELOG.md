# CHANGELOG

All notable changes to this project will be documented in this file. This project make usage of the [Yii Versioning Strategy](https://github.com/yiisoft/yii2/blob/master/docs/internals/versions.md). In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

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
