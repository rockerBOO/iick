<h1>Torrents</h1>
<ul>
<?php

foreach ($vars['torrents'] as $torrent)
{
    ?>
    <li><a href="/details/<?php echo $torrent['_id']; ?>"><?php echo $torrent['name']; ?></a></li>
    <?php
}

?>
</ul>