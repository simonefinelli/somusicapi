<?php

$prefix = OW_DB_PREFIX."somusicapi_";

$sql = '
DROP TABLE '.$prefix.'push_notification;';

OW::getDbo()->query($sql);