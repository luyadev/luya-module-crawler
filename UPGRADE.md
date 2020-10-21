# LUYA ADMIN MODULE UPGRADE

This document will help you upgrading from a LUYA admin module version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

## from 2.0 to 3.0

+ Run the migrate command, as new migrations are available.
+ classes `src/frontend/classes/CrawlPage.php` and `src/frontend/classes/CrawlContainer.php` has been removed.
+ The following `luya\crawler\frontend\Module` properties has beem removed: `$doNotFollowExtensions`, `useH1`

## from 1.0 to 2.0

+ This release contains the new migrations which are required for the user and file table. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.