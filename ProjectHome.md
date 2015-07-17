Projects aims to provide a simplified, maintainable version of the official PHP library.
It is a fork of amazon-mws-v20090101-php-2011-08-01._V157090117_.

Reasons to start this project is strong code redundancy (>50%) and many bad practices in the original implementation making it hard to maintain and hard to test.
A further reason was the missing MarketplaceIdList feature prior to 2011-08-01 (de-version) which caused quiet some effort to debug existing programs against API changes made earlier in 2011.

Aim of this project is to provide a single, easily maintainable library for all MWS APIs.

Testing is done against PHP 5.3.2 (default for Ubuntu 10.04 LTS).
Might work down to 5.2.8. cURL 7.18.0 is used.
For further details see https://developer.amazonservices.de/doc/bde/feeds/v20090901/php.html

Additional requirements:
  * support for pear-like autoloading (as proposed by the samples .config.inc.php)
(since this is standard for many recent frameworks and easily added to standard php)