*/5 * * * * www-data  if [ -f /var/lib/typo3-dummy/typo3/cli_dispatch.phpsh ]; then /usr/bin/php5 /var/lib/typo3-dummy/typo3/cli_dispatch.phpsh scheduler ; fi

