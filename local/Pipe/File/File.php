<?php
namespace Pipe\File;

class File
{
    /**
     * @var string
     */
    protected $filePath = '';

    /**
     * @var string
     */
    protected $resultDirPath = '';

    /**
     * @var string
     */
    protected $fileName = '';

    public function remove()
    {
        $file = $this->getFilePath();

        if(file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * @param $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param $resultDir
     * @return $this
     */
    public function setResultDirPath($resultDir)
    {
        $this->resultDirPath = $resultDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultDirPath()
    {
        return $this->resultDirPath;
    }

    /**
     * @param $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getUniqueFilename($fileName) {
        if(!$fileName) {
            return '';
        }

        $type = strtolower(strrchr($fileName, "."));
        $name = rtrim($fileName, $type);

        $filePath = $this->getResultDirPath();

        $i = 1;
        while(true) {
            if(!file_exists($filePath . '/' . $fileName)) {
                return $fileName;
            }

            $fileName = $name . '_' . $i . $type;
            $i++;
        }
    }

    static public function getClearFileName($filename)
    {
        return rtrim(basename($filename), strrchr($filename, "."));
    }

    static public function renameFile($filename, $newName)
    {
        $type = strtolower(strrchr($filename, "."));
        return $newName . $type;
    }
}
