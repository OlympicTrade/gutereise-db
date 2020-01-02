<?php
namespace Pipe\Url;

use Application\Common\Model\Module;

class Url
{
    static public function url($options = [], $path = [])
    {
        $options = $options + [
            'platform'  => '',
            'module'    => null,
            'section'   => null,
            'action'    => null,
            'id'        => null,
        ];

        if(!$options['module']) {
            $moduleMdl = Module::getInstance();
            $module    = $moduleMdl->module;
            $section   = $moduleMdl->section;
        } else {
            $module    = $options['module'];
            $section   = $options['section'];
        }

        $url = $options['platform'] . '/' . $module;

        if($module != $section) {
            $url .= '-' . $section;
        }

        $url .= '/';

        if($options['module']) {
            $url .= $options['module'] . '/';
        }

        foreach ($path as $arg) {
            $url .= $arg . '/';
        }

        return strtolower($url);
    }
}
/*
class Url
{
    static public function getUrl($query = array(), $hash = array(), $baseUri = '', $clear = false)
    {
    	if(empty($baseUri)) {
    		$baseUri = $_SERVER['REQUEST_URI'];
    	}
    	
    	if(strripos($baseUri, '?'))
    	{
    		$tmp = explode("?", $baseUri);
    		$baseUri = $tmp[0];
    		
    		if(!$clear) {
	    		$getOld = explode("&", $tmp[1]);
	    		
	    		foreach ($getOld as $g)
	    		{
	    			$tmp = explode("=", $g);
	    			
	    			if(!array_key_exists($tmp[0], $query)) {
	    				$query[$tmp[0]] = $tmp[1];
	    			}
	    		}
    		}
    	}

        if(!empty($query)) {
            $first = true;
            foreach ($query as $key => $value) {
                $baseUri .= ($first ? '?' : '&') . $key . '=' . $value;
                $first = false;
            }
        }

        if(!empty($hash)) {
            $first = true;
            foreach ($hash as $key => $value) {
                $baseUri .= ($first ? '#' : '&') . $key . '=' . $value;
                $first = false;
            }
        }

    	return $baseUri;
    }
	
	static public function isLocal($url)
    {
		$section = substr($url, 0, 3);
		
		if(in_array($section, array('www', 'htt', 'ftp'))) {
			return false;
		} 
		return true;
	}
}*/