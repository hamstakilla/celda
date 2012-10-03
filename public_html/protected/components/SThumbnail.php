<?php

class SThumbnail extends CComponent {
  const JPG = 0;  
  const GIF = 1;
  const PNG = 2;
  const JPEG = 3;
  private $_image;
  private $_thumbnail;
  private $_thumbnailWidth;
  private $_thumbnailHeight;
  private $_thumbnailDir;
  private $_imageWidth;
  private $_imageHeight;
  private $_imageDir;
  private $_imageType;
  private $_imageBaseName;
  private $_imageFileName;
  private $_thumbnailBaseName;
  private $_thumbnailFileName;
  private $_thumbnailType;
  private $_sourceImage;
  private $_destinationImage;

/**
 * Creates a thumbnail of an image
 * @param String $image The full path of the original image
 * @param String $thumbnail The full path of the thumbnail to create
 * @param int $thumbWidth The thumbnail width
 * @return boolean true if the thumbnailer was created
 */

  public function __construct($image,$thumbnail = "" ,$thumbWidth = 150) {

    $this->_image = $image;
    $imageInfo = pathinfo($image);
    $this->_imageBaseName = $imageInfo['basename'];
    $this->_imageFileName = $imageInfo['filename'];
    $this->_imageDir =  $imageInfo['dirname'];
    $this->_imageType=  $this->getImageType(strtolower($imageInfo['extension']));

    if(!$thumbnail) {
      $this->_thumbnailBaseName = $this->_imageFileName."_thumb.".$this->getImageTypeText($this->_imageType);
      $this->_thumbnailFileName = $this->_imageFileName."_thumb";
      $this->_thumbnailDir = $this->_imageDir;
      $this->_thumbnailType=  $this->_imageType;
    } else {
      $thumbnailInfo = pathinfo($thumbnail);
      $this->_thumbnailBaseName = $thumbnailInfo['basename'];
      $this->_thumbnailFileName = $thumbnailInfo['filename'];
      $this->_thumbnailDir =  $thumbnailInfo['dirname'];
      $this->_thumbnailType=  $this->getImageType(strtolower($imageInfo['extension']));
    }

    $this->_thumbnailWidth = $thumbWidth;
    switch ($this->_imageType) {
      case 0:
      $this->_sourceImage=imagecreatefromjpeg($this->_thumbnailDir."/".$this->_imageBaseName);
      break;
      case 1:
      $this->_sourceImage=imagecreatefromgif($this->_thumbnailDir."/".$this->_imageBaseName);
      break;
      case 2:
      $this->_sourceImage=imagecreatefrompng($this->_thumbnailDir."/".$this->_imageBaseName);
      break;
      case 3:
      $this->_sourceImage=imagecreatefromjpeg($this->_thumbnailDir."/".$this->_imageBaseName);
      break;
      default:
      return false;
      break;
    }

    return true;
  }



  public function createthumb() {
    $this->_imageWidth = imageSX($this->_sourceImage);
    $this->_imageHeight=imageSY($this->_sourceImage);
    $this->_thumbnailHeight=$this->_imageHeight*($this->_thumbnailWidth/$this->_imageWidth);
    $this->_destinationImage=ImageCreateTrueColor($this->_thumbnailWidth,$this->_thumbnailHeight);

    imagecopyresampled($this->_destinationImage,
      $this->_sourceImage,
      0,0,0,0,
      $this->_thumbnailWidth,
      $this->_thumbnailHeight,
      $this->_imageWidth,
      $this->_imageHeight);
    switch ($this->_thumbnailType) {
      case 0:
      imagejpeg($this->_destinationImage,$this->_thumbnailDir."/".$this->_thumbnailBaseName);
      break;
      case 1:
      imagegif($this->_destinationImage,$this->_thumbnailDir."/".$this->_thumbnailBaseName);
      break;
      case 2:
      imagepng($this->_destinationImage,$this->_thumbnailDir."/".$this->_thumbnailBaseName);
      break;
      case 3:
      imagejpeg($this->_destinationImage,$this->_thumbnailDir."/".$this->_thumbnailBaseName);
      break;
      default:
      return false;
      break;
    }

    imagedestroy($this->_destinationImage);
    imagedestroy($this->_sourceImage);
  }



  private function getImageType($type) {
    if (preg_match('/jpg/',$type)) {
      return self::JPG;
    }
    else if (preg_match('/png/',$type)) {
      return self::PNG;
    }
    else if (preg_match('/gif/',$type)) {
      return self::GIF;
    }
    else if (preg_match('/jpeg/',$type)) {
      return self::JPEG;
    }
  }

  private function getImageTypeText($type){
    switch ($type) {
      case 0:
      return "jpg";
      break;
      case 1:
      return "gif";
      break;
      case 2:
      return "png";
      break;
      case 3:
      return "jpeg";
      break;
      default:
      return false;
      break;
    }
  }



  public function getThumbnailBaseName(){
    return $this->_thumbnailBaseName;
  }
}
