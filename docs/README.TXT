Project home page (Russian): http://bolk.exler.ru/files/xbm/

Usage:

    include_once 'Image/XBM.php';

    $out = new Image_XBM;
    $out->create($sx, $sy);

    header('Content-type: image/xbm');
    $out->output();