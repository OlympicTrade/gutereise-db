<?php
namespace Pipe\Db\Plugin;

use Pipe\File\Image as PipeImage;
use Pipe\File\File as PipeFile;

class Image extends PluginAbstract
{
    const THUMBS_PATH = '/files/thumbs';

    const ERROR_FORMAT = 1;
    const ERROR_SIZE   = 2;
    const ERROR_FILE   = 3;

    /**
     * @var string
     */
    protected $maxFileSize = 2000000;

    /**
     * @var string
     */
    protected $folder = null;

    /**
     * @var array
     */
    protected $resolutions = array();

    /**
     * @var array
     */
    protected $image = array(
        'filename'  => '',
        'desc'      => '',
    );

    /**
     * @var array
     */
    protected $imageNew = null;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var bool
     */
    protected $changed = false;

    /**
     * @param string $folder
     * @return $this
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @param array $versions
     */
    public function addResolutions($versions)
    {
        foreach($versions as $prefix => $options) {
            $this->addResolution($prefix, $options);
        }
    }

    public function addResolution($prefix, $options)
    {
        $default = array(
            'width'       => 3000,
            'height'      => 3000,
            'crop'        => false,
            'watermark'   => false,
        );

        $options = array_merge($default, $options);

        if(array_key_exists($prefix, $this->resolutions)) {
            throw new \Exception('Image version "' . $prefix . '" already exists');
        }

        if($prefix == '') {
            throw new \Exception('Image prefix can\'t be empty');
        }

        $this->resolutions[$prefix] = array(
            'width'      => $options['width'],
            'height'     => $options['height'],
            'watermark'  => $options['watermark'],
            'crop'       => $options['crop'],
            'updated'    => false
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function hasImage()
    {
        $this->load();

        return $this->image['filename'] ? true : false;
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function getImage($prefix)
    {
        $this->load();

        $filename = $this->image['filename'] ? $this->image['filename'] : 'default.png';
        $fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder;
        $urlPath = self::THUMBS_PATH . '/' . $this->folder . '/' . $prefix . '/' . $filename;

        if(file_exists($fullPath . '/' . $prefix . '/' . $filename)) {
            return $urlPath;
        }

        $imageObj = new PipeImage();
        $imageObj->setFilePath($fullPath . '/' . $filename);
        $imageObj->setResultDirPath($fullPath . '/' . $prefix);

        $imageObj->setOptions(array(
            'width'  => $this->resolutions[$prefix]['width'],
            'height' => $this->resolutions[$prefix]['height'],
            'crop'   => $this->resolutions[$prefix]['crop'],
        ));

        $imageObj->createThumbnail();

        return $urlPath;
    }

    public function setImage($data)
    {
        $this->load();

        $filePath = $data['filePath'];

        if($filePath && file_exists($filePath)) {
            list($width, $height, $type) = getimagesize($filePath);

            if(!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
                return self::ERROR_FORMAT;
            }

            if($this->maxFileSize < filesize($filePath)) {
                return self::ERROR_SIZE;
            }

            $this->imageNew = array(
                'filePath'  => $filePath,
            );
        }

        if($data['fileName']) {
            $this->imageNew['fileName'] =  \Pipe\String\Translit::url($data['fileName']);
        } else {
            $this->imageNew['fileName'] = \Pipe\String\String::randomString();
        }

        if($data['desc']) {
            $this->imageNew['desc'] = $data['desc'];
        }

        $this->changed = true;

        return 0;
    }

    public function getFileName()
    {
        return PipeFile::getClearFileName($this->image['filename']);
    }

    public function getDesc()
    {
        return $this->image['desc'];
    }

    public function load()
    {
        $parentId = $this->getParentId();

        if(!$parentId) {
            return $this;
        }

        if($this->loaded) {
            return $this;
        }

        if($this->cacheLoad()) {
            $this->loaded = true;
            return $this;
        }

        $select = $this->getSelect()
            ->where(array('t.' . $this->parentFiled => $parentId));

        $result = $this->fetchRow($select);

        if($result) {
            $this->fill($result);
        }

        $this->loaded = true;

        $this->cacheSave($result);

        return $this;
    }

    public function updateImageFile() {
        if(empty($this->imageNew)) {
            return true;
        }

        $this->load();

        $imageObj = new PipeImage();

        if($this->image['filename']) {
            $oldFilename = $this->image['filename'];

            if(!$this->imageNew['filePath']) {
                $this->imageNew['filePath'] = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $oldFilename;
            }
        }

        $filePath = $this->imageNew['filePath'];
        $fileName = $this->imageNew['fileName'];

        $fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder;

        $imageObj->setFileName($fileName);
        $imageObj->setFilePath($filePath);
        $imageObj->setResultDirPath($fullPath);

        $fileName = $imageObj->save();

        if($fileName) {
            $this->image['filename'] = $fileName;
        }

        if(isset($oldFilename)) {
            foreach($this->resolutions as $prefix => $resolution) {
                $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $prefix . '/' . $oldFilename);
                $imageObj->remove();
            }

            $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $oldFilename);
            $imageObj->remove();
        }

        return $this;
    }

    public function save($transaction = false)
    {
        if(!$this->changed) {
            return true;
        }

        $this->load();

        $this->updateImageFile();

        if(!$this->id) {
            $insert = $this->insert();
            $insert->values(array(
                'filename' => $this->image['filename'],
                'desc'     => $this->image['desc'],
                $this->parentFiled => $this->getParentId(),
            ));

            $this->execute($insert);

            $this->id = $this->adapter->getDriver()->getLastGeneratedValue();
        } else {
            $update = $this->update();
            $update->where(array($this->primary => $this->id));

            $update->set(array(
                'filename' => $this->image['filename'],
                'desc'     => $this->image['desc'],
            ));

            $this->execute($update);
        }

        $this->cacheClear();

        return true;
    }

    public function remove()
    {
        $this->load();

        $imageObj = new PipeImage();

        if($this->image['filename']) {
            foreach($this->resolutions as $prefix => $resolution) {
                $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $prefix . '/' . $this->image['filename']);
                $imageObj->remove();
            }

            $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $this->image['filename']);
            $imageObj->remove();
        }

        $delete = $this->delete();
        $delete->where(array(
            $this->parentFiled => $this->getParentId(),
        ));

        $this->execute($delete);

        $this->image = array(
            'filename'  => '',
            'desc'      => '',
        );

        $this->id = 0;

        $this->changed = false;

        $this->cacheClear();

        return true;
    }

    /**
     * @param $data
     * @return Image
     */
    public function fill($data)
    {
        $this->image['filename'] = $data['filename'];
        $this->image['desc']     = $data['desc'];
        $this->id                = $data['id'];

        $this->loaded = true;

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCacheName($name = '') {
        return 'db-plugin-image-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }

    /**
     * @return bool
     */
    protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName($this->getParentId());

        if($data = $this->getCacheAdapter()->getItem($cacheName)) {
            $this->fill($data);
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function cacheSave($data)
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName($this->getParentId());

        $this->getCacheAdapter()->setItem($cacheName, $data);
        $this->getCacheAdapter()->setTags($cacheName, array($this->table()));

        return true;
    }

    /**
     * @return bool
     */
    protected function cacheClear()
    {
        if(!$this->getCacheAdapter()) {
            return false;
        }

        $this->getCacheAdapter()->clearByTags(array($this->table()));
        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    public function unserializeArray($data)
    {
        if(!isset($data['image']) || !is_array($data['image'])) {
            return true;
        }

        $imageInfo = $data['image'];

        if(isset($imageInfo['del']) && $imageInfo['del'] == 'on') {
            $this->remove();
            return true;
        }

        if($imageInfo['filePath']) {
            $imageInfo['filePath'] = PUBLIC_DIR . $imageInfo['filePath'];
        }

        $this->setImage($imageInfo);

        return true;
    }

    /**
     * @param $result
     * @param string $prefix
     * @return array
     */
    public function serializeArray($result, $prefix = '')
    {
        $this->load();

        $result[$prefix . 'file'] = '';

        return $result;
    }
}