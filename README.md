# Tooso Stock Synchronization for Magento

## Description

This extension add stock synchronization feature to Tooso


## Requirements


The only requirement for the module is the default Magento cronjob execution correctly configured.
Something like the following is a good configuration:

```
*/5 * * * * php -f /absolute/path/to/magento/cron.sh > /dev/null 2>&1
```

This will run the Magento jobs schedule and executions every 5 minutes. *Note:* the Tooso indexing flow start every 15 minutes.

Here is some additional info on how to add cron jobs with Cpanel and Plesk, which are some of the most popular web panels:

* [CPanel - Add a cron job](https://documentation.cpanel.net/display/ALD/Cron+Jobs#CronJobs-Addacronjob)
* [Plesk - How do I set up cron jobs](https://www.interspire.com/support/kb/questions/382/How+do+I+set+up+CRON+on+my+server+using+Plesk%3F)

If you can't access your server configuration, please ask your hosting provider to configure crontab for you.

## External Dependencies

[magento-module-tooso-search](https://github.com/tooso/magento-module-tooso-search)
