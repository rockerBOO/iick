<?php

class Peer extends Base {
    public $fields = array(
        'torrent',
        'user_id',
        'ip',
        'port',
        'uploaded',
        'downloaded',
        'to_go',
        'seeder',
        'started',
        'last_action',
        'connectable',
        'agent',
        'finished_at',
        'download_offset',
        'upload_offset',
    );
}

?>