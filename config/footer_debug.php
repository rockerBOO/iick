<div style="width: 100%; height: 500px; overflow:auto;">
    <pre><?php
    
    $timers = Timer::report();
    
    arsort($timers);

    foreach ($timers as $key => $timer)
    {
        echo str_pad(round($timer, 8), 10, ' ') . ' - ' . $key . "\n";
    }
    
    ?></pre>
</div>