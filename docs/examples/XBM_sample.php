<?
/**
 * Image_XBM example script.
 * Create XBM, copy photo, draw photo frame and
 * (if Text_Figlet available) draw some text
 *
 * @package Image_XBM
 */

include_once 'Image/XBM.php';

$xbm = &new Image_XBM;
$xbm->createFromFile('photo.xbm');
$sx = $xbm->getWidth();
$sy = $xbm->getHeight();

$out = &new Image_XBM;
$out->create($sx, $sy);

// Draw photoframe
$out->setstyle(array(IMAGE_XBM_BLACK, IMAGE_XBM_WHITE, IMAGE_XBM_BLACK, IMAGE_XBM_BLACK, IMAGE_XBM_BLACK));
$out->drawrectangle(0, 0, $sx-1, $sy-1, IMG_COLOR_STYLED);

$out->copy($xbm, 5, 5, 5, 5, $sx-10, $sy-10);
$out->drawfiglettext('Hello, Julia!', 'xbriteb.flf', '2x2', IMAGE_XBM_BLACK, IMAGE_XBM_TRANS, 10, 10);
$out->drawfiglettext(' Image_XBM Package ', '6x10.flf', 1, IMAGE_XBM_BLACK, IMAGE_XBM_WHITE, $sx-145, $sy-20);

header('Content-type: image/xbm');
$out->output();
?>