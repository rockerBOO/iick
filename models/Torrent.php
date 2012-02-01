<?php

class Torrent extends Base {
    public $fields = array(
        'name',
        'info_hash',
        'filename',
        'descr',
        'size',
        'added',
        'numfiles',
        'comments',
        'views',
        'times_completed',
        'leechers',
        'seeders',
        'last_action',
        'visible',
        'banned',
    );
}

?>