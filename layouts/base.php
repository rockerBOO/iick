<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>IICK Bit Torrent Tracker</title>
        <style type="text/css">
        body
        {
            padding: 0 2% 0 2%;
            background: #FFF;
            color: #858585;
            font: normal normal 10pt/1.4 Calibri, 'Helvetica Neue', Arial, Sans-serif;
        }

        h1
        {
            font: bold normal 35pt/1.4 Calibri, 'Helvetica Neue', Arial, Sans-serif;
            color: #67CFD3;
        }

        a, a:link, a:active, a:visited
        {
        	color:  #67CFD3;
        	text-decoration: none;
        	padding-bottom: 2px;
        	border-bottom: 1px solid #E0E0E0;
        }

        a:hover
        {
        	color:  #C7FFF6;
        }

        p
        {
        	padding: 4px 0 4px 0;
        	margin: 0;
        	line-height: 1.4;
        }
        
        a img {
            border: 0;
            border-bottom: 0;
            text-decoration: none;
        }
        
        table {
            background: #F0F0F0;
        }
            td {
                background: #fff;
            }
        </style>
    </head>
    <body>
        <div id="body_container">
            <a href="/"><img src="/assets/graphics/logo.png"></a>
            <ul>
                <li><a href="/list">Torrents</a></li>
                <?php
                
                if (false == $vars['user']->isLoggedIn())
                {
                    ?>
                <li><a href="/login">Log In</a></li>
                <li><a href="/signup">Sign Up</a></li>
                    <?php
                    
                }
                else
                {
                    ?>
                <li><a href="/profile">Profile</a></li>
                    <?php
                }
                
                ?>
            </ul>
            {{ OUTPUT_TEMPLATE }} 

        </div>
    </body>
</html>