<?php
namespace Pipe\Compressor;

use Application\Common\Model\Settings;
use Leafo\ScssPhp\Compiler;
use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use Pipe\StdLib\Singleton;

class Compressor {
    use Singleton;

    protected $miniDir   = '/mini/';

    protected $modules   = [];
    protected $platforms = [];

    static protected $instance = null;

    public function addFiles($files, $type, $platform)
    {
        if(!isset($this->platforms[$platform][$type])) {
            $this->platforms[$platform][$type] = [];
        }

        if(is_array($files)) {
            $this->platforms[$platform][$type] = array_merge($this->platforms[$platform][$type], $files);
        } else {
            $this->platforms[$platform][$type][] = $files;
        }

        return $this;
    }

    protected function getFiles($type, $platform)
    {
        $files = $this->platforms[$platform][$type];

        foreach ($files as $file) {
            if(!file_exists($file)) {
                throw new \Exception('File "' . $file . '" not found');
            }
        }

        foreach ($this->modules as $module) {
            $file = MODULE_DIR . '/' . ucfirst($module) . '/view/template/' . strtolower($platform) . '.' . $type;
            if(file_exists($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    public function getLink($type, $platform)
    {
        $mini = $this->getFileName($type, $platform);
        $publicFile = $this->miniDir . $mini;
        $serverFile = PUBLIC_DIR . $publicFile;

        $files = $this->getFiles($type, $platform);

        if(!$mini) {
            return $this->updateFile($files, $type, $platform);
        }

        $miniTime = filemtime($serverFile);
        foreach($files as $file) {
            if(filemtime($file) > $miniTime) {
                return $this->updateFile($files, $type, $platform);
            }
        }

        return $publicFile;
    }

    public function addModule($module)
    {
        $this->modules[] = $module;
    }

    public function getFileName($type, $platform, $new = false)
    {
        $fileMask = PUBLIC_DIR . $this->miniDir . $platform . '_*.' . $type;

        $curFile = glob($fileMask);

        if(!$new) {
            return $curFile ? basename($curFile[0]) : false;
        }

        foreach (glob($fileMask) as $filename) {
            @unlink($filename);
        }

        $newFile =
            $platform . '_' .
            date('Ymd_His') . '.' .
            $type;

        return $newFile;
    }

    public function updateFile($files, $type, $platform)
    {
        $file = $this->getFileName($type, $platform, true);
        $publicFile = $this->miniDir . $file;
        $serverFile = PUBLIC_DIR . $publicFile;

        $content = '';
        foreach($files as $file) {
            if((new \SplFileInfo($file))->getExtension() == 'scss') {
                $scss = new Compiler();
                $content .= $scss->compile(file_get_contents($file));
            } else {
                $content .= file_get_contents($file);
            }
        }

        $h = fopen($serverFile, 'w');
        //fwrite($h, $content);
        fwrite($h, '');
        fclose($h);

        switch($type) {
            case 'css':
                $minifier = new CSS();
                break;
            case 'js':
                $minifier = new JS();
                break;
        }

        $minifier->add($content);
        $minifier->minify($serverFile);

        return $publicFile;
    }
}