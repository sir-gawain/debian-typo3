<?php

    $debian_config = '/etc/typo3-dummy/debian-db.php';

    if(is_file($debian_config) && is_readable($debian_config)) {

        include_once($debian_config);

        $typo_db_username = $dbuser;
        $typo_db_password = $dbpass;
        $typo_db = $dbname;
        $typo_db_host = $dbserver;

        if(empty($typo_db_host)) {
            $typo_db_host = 'localhost';
        }
    }

?>
