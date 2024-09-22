# MoneyLog

## Introduction

This is a web application to track your money.

## Set hooks
```shell
git config core.hooksPath .githooks
```

## crontab
```
# m h  dom mon dow   command
0   0   *   *   *   mysqldump moneylog --no-tablespaces | bzip2 -c > /home/fabio/backup/moneylog_`date +\%Y-\%m-\%d_\%H`.sql.bz2
0   0   *   *   *   find /home/fabio/backup -mtime +7 -delete
1   0   *   *   *   /home/fabio/dropbox_uploader.sh -f /home/fabio/.dropbox_uploader -q upload backup/* .
```
