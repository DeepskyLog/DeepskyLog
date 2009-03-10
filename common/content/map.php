<?php  // These are the coordinates the location we wish to plot. These are being passed in the URL, but we will set them to a default if nothing is passed. if(empty($long))$long = -70.7333; if(empty($lat)) $lat = -29.25;
$long = $_GET['long'];
$lat = $_GET['lat'];
// First we load the background/base map. We assume it's located in same dir as the script. This can be any format but we are using JPG in this example. We will also allocate the color for the marker 
$im = imagecreatefromjpeg("../../styles/images/earth.jpg");
$red = imagecolorallocate ($im, 255,0,0);
// Next need to find the base image size. We need these variables to be able scale the long/lat coordinates.
$scale_x = imagesx($im);
$scale_y = imagesy($im); 
$pt = getlocationcoords($lat, $long, $scale_x, $scale_y);
// Now mark the point on the map using a red 4 pixel rectangle 
imagefilledrectangle($im,$pt["x"]-2,$pt["y"]-2,$pt["x"]+2,$pt["y"]+2,$red);
//imagestring($im,2,1,$scale_y-20,"Courtesy of www.staycanada.ca",$red);
// Return the map image. We are using a PNG format as it gives better final image quality than a JPG
header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
function getlocationcoords($lat, $lon, $width, $height)
{ $x = (($lon + 180) * ($width / 360));
  $y = ((($lat * -1) + 90) * ($height / 180));
  return array("x"=>round($x),"y"=>round($y));
}
// Now we convert the long/lat coordinates into screen coordinates
?>
