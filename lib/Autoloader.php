<?php

class Autoloader
{
    private $isExistsClass = false;
    private $classPath;
    private $basefolder = ROOT."/lib/";
    public function __construct($class)
    {
        $class = $this->normalizeClassName($class);
        $recursiveDirectoryIterator = new RecursiveDirectoryIterator(ROOT.'/lib/');
        foreach(new RecursiveIteratorIterator($recursiveDirectoryIterator) as $file => $key)
        {
            $pathinfo = pathinfo($file);
            if($pathinfo['extension'] != 'php')continue;

            if($class.'.php' == $pathinfo['basename'])
            {
                $this->isExistsClass = true;
                $this->classPath = $file;
            }
        }
    }

    public function normalizeClassName($class)
    {
        if(stristr($class, '\\') === false)return $class;

        $classes = explode("\\", $class);
        return $classes[count($classes)-1];
    }
    
    public function isExistsClass()
    {
        return $this->isExistsClass;
    }

    public function getPath()
    {
        return $this->classPath;
    }
}