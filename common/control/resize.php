<?php // utility functions in resizing
if((!isset($inIndex))||(!$inIndex)) include "../../redirect.php";
else
{
function image_createThumb($src,$dest,$maxWidth,$maxHeight,$quality=75) {
   if (file_exists($src)  && isset($dest)) {
       // path info
       $destInfo  = pathInfo($dest);

       // image src size
       $srcSize  = getImageSize($src);

       // image dest size $destSize[0] = width, $destSize[1] = height
       $srcRatio  = $srcSize[0]/$srcSize[1]; // width/height ratio
       $destRatio = $maxWidth/$maxHeight;
       if ($destRatio > $srcRatio) {
           $destSize[1] = $maxHeight;
           $destSize[0] = $maxHeight*$srcRatio;
       }
       else {
           $destSize[0] = $maxWidth;
           $destSize[1] = $maxWidth/$srcRatio;
       }

       // path rectification
       if ($destInfo['extension'] == "gif") {
           $dest = substr_replace($dest, 'jpg', -3);
       }

       // true color image, with anti-aliasing
       $destImage = imageCreateTrueColor($destSize[0],$destSize[1]);
//       imageAntiAlias($destImage,true);

       // src image
       switch ($srcSize[2]) {
           case 1: //GIF
           $srcImage = imageCreateFromGif($src);
           break;

           case 2: //JPEG
           $srcImage = imageCreateFromJpeg($src);
           break;

           case 3: //PNG
           $srcImage = imageCreateFromPng($src);
           break;

           default:
           return false;
           break;
       }

       // resampling
       imageCopyResampled($destImage, $srcImage, 0, 0, 0, 0,$destSize[0],$destSize[1],$srcSize[0],$srcSize[1]);

       // generating image
       switch ($srcSize[2]) {
           case 1:
           case 2:
           imageJpeg($destImage,$dest,$quality);
           break;

           case 3:
           imagePng($destImage,$dest);
           break;
       }
       return true;
   }
   else {
       return 'No such File';
   }
}
}
?>
