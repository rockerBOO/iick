<?php

class Template 
{
    const DEBUG_MODE = false;
    
    public $templatePath          = '/webroot/iick/templates/';
    public $layoutPath            = '/webroot/iick/layouts/';
    public $templateFileExtension = '.php';
    public $templateTag           = 'template';
        
    public function load($template, $layout='', $vars=array()) 
    {
            $this->loadingTk = Timer::start('Loading template');
        
        // Loading a new template, unset all old data
        $this->output = '';
        $this->template = '';
        $this->layout = '';
        $this->vars = $vars;

        $this->load_template($template);

        if ('' != $layout)
        {
            $this->load_layout($layout);
            
            $this->output = str_replace('{{ OUTPUT_TEMPLATE }}', $this->template, $this->layout);
        }
        else
        {
            $this->output = $this->template;
        }
        
        return $this;
    }
    
    public function load_template($template)
    {
        $templateFile = $this->templatePath . $template . $this->templateFileExtension;
        
        if (false == file_exists($templateFile))
        {
            die('Template ' . $templateFile . ' does not exist');
        }
        
        $vars = $this->vars;
        
        // ob_end_flush();
        ob_start();
        
        require($templateFile);
        
        $this->template = ob_get_clean();
    }
    
    public function load_layout($layout)
    {
        $layoutFile   = $this->layoutPath . $layout . $this->templateFileExtension;
        
        if (false == file_exists($layoutFile))
        {
            die('Layout ' . $layoutFile . ' does not exist');
        }
        
        $vars = $this->vars;
        
        // ob_end_flush();
        ob_start();
        
        require($layoutFile);
        
        $this->layout = ob_get_clean();
    }
    
    public function parse($template='')
    {
        if ('' == $template)
        {
            $template = $this->output;
        }
        
        preg_match_all('#<' . $this->templateTag . ' ([^>]*)>#', $template, $matches);
    
        // print_r_html($matches);
    
        if (isset($matches))
        {
            foreach ($matches[1] as $key => $match)
            {
                preg_match_all('#(\w+)\=\"([\d\w][^\"]*)\"#', $match, $matchArgs);
            
                // print_r_html($matchArgs, $match);
                // continue;
            
                if (false == $matchArgs)
                {
                    continue;
                }
            
                $replaceValue = false;
            
                // Loop through matches
                foreach ($matchArgs[0] as $k => $matchArg)
                {
                    // Get result get="title"
                    if ($matchArgs[1][$k] == 'get')
                    {
                        if (strstr($matchArgs[2][$k], '.'))
                        {
                            // $replaceValue = templateGetVarHierarchy($matchArgs[2][$k], $vars);
                            // print_r_html($replaceValue);
                        
                            $vals = explode('.', $matchArgs[2][$k]);
                        
                            $var = $this->vars[$vals[0]];
                            unset($vals[0]);
                        
                            foreach ($vals as $val)
                            {
                                if (false == isset($var[$val]))
                                {
                                    echo 'Error finding ' . $matchArgs[2][$k] . ' in $vars'."\n";
                                    break 2;
                                }
                            
                                $var = $var[$val];
                            }
                        
                            $replaceValue = $var;
                        }
                        else
                        {
                            $key = $matchArgs[2][$k];
                        
                            if (isset($this->vars[$key]))
                            {
                                $replaceValue = $this->vars[$key];
                            }
                            else
                            {
                                echo 'Error finding ' . $key . ' in $vars'."\n";
                                break;
                            }
                        }
                    }
                    // Loop through results for="video in videos"
                    elseif ($matchArgs[1][$k] == 'for')
                    {
                        // looping these results
                        list($key, $x, $keys) = explode(' ', $matchArgs[2][$k]);
                    
                        $regex = '#<' . $this->templateTag . ' [^>]*>(.*)<\/' . $this->templateTag . '>#m';
                    
                        preg_match_all($regex, $template, $forMatch);
                    
                        echo htmlspecialchars(print_r($forMatch, true));
                    
                        // Get the full group of data
                        foreach ($this->vars[$keys] as $k => $v)
                        {
                            ?>
                        
                            <?php
                        }
                    
                    
                        echo '' . $matchArgs[2][$k];
                    }
                    // Load the widget widget="about"
                    elseif ($matchArgs[1][$k] == 'widget' || $matchArgs[1][$k] == 'load')
                    {
                        ob_start();

                        include($this->templatePath . 'modules/' . $matchArgs[2][$k]. '.php');   

                        $replaceValue = ob_get_clean();
                    
                        $replaceValue = $this->parse($replaceValue);
                    }
                    // Check conditional
                    elseif ($matchArgs[1][$k] == 'if')
                    {
                        // check value;
                    
                    
                    }
                }                

                if ($replaceValue !== false)
                {
                    if (Template::DEBUG_MODE)
                    {
                        echo 'Replacing ' . htmlspecialchars('<' . $this->templateTag . ' ' . $match . '>') ."\n<br />";
                    }
                
                    $template = str_replace('<' . $this->templateTag . ' ' . $match . '>', $replaceValue, $template);
                }
                elseif (Template::DEBUG_MODE)
                {
                    echo 'Value not found for ' . $match ."\n<br />";
                }
                else
                {
                    $template = str_replace('<' . $this->templateTag . ' ' . $match . '>', '', $template);
                }
            }
        }
    
        $this->output = $template;
    
        return $this;
    }
    
    public function output()
    {        
        echo $this->output; 
        
            Timer::stop($this->loadingTk);
            Timer::stop('TOTAL');
            
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '67.164.186.56')
        {
            require(ROOT_PATH  . 'config/footer_debug.php');
        }
        
        exit;
    }
}

?>