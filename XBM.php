<?php

/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Evgeny Stepanischev <bolk@adme.ru>                          |
// +----------------------------------------------------------------------+
// Project home page (Russian): http://bolk.exler.ru/files/xbm/
//
// $Id$

require_once "PEAR.php";

/**
 * Black color constant
 */
define ('IMAGE_XBM_BLACK', 0);

/**
 * White color constant
 */
define ('IMAGE_XBM_WHITE', 1);

/**
 * Transparency
 */
define ('IMAGE_XBM_TRANS', 2);

class Image_XBM {

   /**
    * Picture width
    *
    * @var integer
    *
    * @access private
    */
    var $_sx;

   /**
    * Picture height
    *
    * @var integer
    *
    * @access private
    */
    var $_sy;   

   /**
    * Picture itself
    *
    * @var array
    *
    * @access private
    */
    var $_image;

   /**
    * Contains style for line drawing
    *
    * @var array
    *
    * @access private
    */
    var $_style;

   /**
    * Position counter in $_style array
    *
    * @var integer
    *
    * @access private
    */
    var $_stylecnt;

    /**
     * Constructor. Init all variables.
     *
     * @access public
     */
    function Image_XBM()
    {
        $this->_sx = 0;
        $this->_sy = 0;
        $this->_image = NULL;

        $this->_style = array(1);
        $this->_stylecnt = 0;

        if (!defined('IMG_COLOR_STYLED')) {
        	define ('IMG_COLOR_STYLED', -1);
		}
    }

    /**
     * Create a new image
     *
     * @param int $x_size image width
     * @param int $y_size image height
     * @access public
     * @return bool always true
     */
    function create($x_size, $y_size)
    {
        $this->_sx = $x_size;
        $this->_sy = $y_size;


        $col = array_pad (array(), $y_size, 0);
        $this->_image = array_pad (array(), ceil($x_size / 8), $col);

        return true;
    }

    /**
     * Get image width
     *
     * @return int returns the width of the image
     * @access public
     */
    function getWidth()
    {
        return $this->_sx;
    }

    /**
     * Get image height
     *
     * @return int returns the height of the image
     * @access public
     */
    function getHeight()
    {
        return $this->_sy;
    }

    /**
     * Draw a line
     *
     * @param int $x1 from X
     * @param int $y1 from Y
     * @param int $x2 to X
     * @param int $y2 to Y
     * @param int $color line color
     * @return bool always true
     * @access public
     */
    function drawLine($x1, $y1, $x2, $y2, $color)
    {
        $step = 0;
        $dx   = abs($x2 - $x1);
        $sx   = ($x2 - $x1) > 0 ? 1 : -1;
        $dy   = abs($y2 - $y1);
        $sy   = ($y2 - $y1) > 0 ? 1 : -1;

        if ($dy > $dx) {
            $step = 1;
            list ($x1, $y1) = array ($y1, $x1);
            list ($dx, $dy) = array ($dy, $dx);
            list ($sx, $sy) = array ($sy, $sx);
        }


        $e = 2 * $dy - $dx;

        for ($i = 0; $i < $dx; ++$i) {
            $step ? $this->drawPixel ($y1, $x1, $color) :
                    $this->drawPixel ($x1, $y1, $color);

            while ($e >= 0) {
                $y1 += $sy;
                $e  -= 2 * $dx;
            }

            $x1 += $sx;
            $e  += 2 * $dy;
        }

        $this->drawPixel ($x2, $y2, $color);
        return true;
    }

    /**
     * Draw a single pixel
     *
     * @param int $x X ordinate
     * @param int $y Y ordinate
     * @param int $color pixel color
     * @return bool always true
     * @access public
     */
    function drawPixel($x, $y, $color)
    {
        if ($x >= 0 && $y >= 0 && $x < $this->_sx && $y < $this->_sy && $color <> IMAGE_XBM_TRANS) {

            if ($color == IMG_COLOR_STYLED) {
                $color = $this->_style[$this->_stylecnt++];

                if ($this->_stylecnt >= sizeof($this->_style)) {
	                $this->_stylecnt = 0;
				}
            }

            if ($color) {
            	$this->_image[$x >> 3][$y] &= 255 ^ (1 << ($x % 8));
			} else {
	            $this->_image[$x >> 3][$y] |= 1 << ($x % 8);
			}
        }

        return true;
    }

    /**
     * Get the index of the color of a pixel
     *
     * @param int $x X ordinate
     * @param int $y Y ordinate
     * @return mixed returns color of the pixel or null if pixel out of bounds
     * @access public
     */
    function getColorAt($x, $y)
    {
        if ($x >= 0 && $y >= 0 && $x < $this->_sx && $y < $this->_sy) {
	        return $this->_image[$x >> 3][$y] & (1 << ($x % 8)) ? 0 : 1;
		}

        return null;
    }

    /**
     * Output image to browser or file
     *
     * @param string $filename (optional) filename for output
     * @return bool PEAR_Error or true
     * @access public
     */
    function output($filename = false)
    {
        $wx = ceil($this->_sx / 8);

        $s = "#define _width ".$this->_sx."\n".
             "#define _height ".$this->_sy."\n".
             "static unsigned char _bits[] = {\n";

        for ($y = 0; $y < $this->_sy; ++$y) {
	        for ($x = 0; $x < $wx; ++$x) {
	        	$s .= '0x'.sprintf('%02x', $this->_image[$x][$y]).', ';
			}
		}

        $s = rtrim ($s, ', ')."\n}";

        if ($filename === false) {
            echo $s;
        } else {
            if ($fp = fopen($filename, 'w')) {
                flock($fp, LOCK_EX);

                fwrite($fp, $s);
                fclose($fp);
            } else {
	            return PEAR::raiseError('Cannot open file for writting.', 5);
            }
        }

        return true;
    }

    /**
     * Draw a rectangle
     *
     * @param int $x1 left coordinate
     * @param int $y1 top coordinate
     * @param int $x2 right coordinate
     * @param int $y2 bottom coordinate
     * @param int $color drawing color
     * @return bool always true
     * @access public
     */
    function drawRectangle($x1, $y1, $x2, $y2, $color)
    {
        $this->drawline ($x1, $y1, $x2, $y1, $color);
        $this->drawline ($x2, $y1, $x2, $y2, $color);
        $this->drawline ($x2, $y2, $x1, $y2, $color);
        $this->drawline ($x1, $y2, $x1, $y1, $color);

        return true;
    }

    /**
     * Draw a filled rectangle
     *
     * @param int $x1 left coordinate
     * @param int $y1 top coordinate
     * @param int $x2 right coordinate
     * @param int $y2 bottom coordinate
     * @param int $color drawing color
     * @return bool always true
     * @access public
     */
    function drawFilledRectangle($x1, $y1, $x2, $y2, $color)
    {
        $hx = min(max($x1, $x2), $this->_sx - 1);
        $lx = max(min($x1, $x2), 0);

        for ($x = $lx; $x <= $hx; ++$x) {
            $this->drawline ($x, $y1, $x, $y2, $color);
        }

        return true;
    }

    /**
     * Draw a polygon
     *
     * @param array $points is a PHP array containing the polygon's vertices
     * @param int $num_points is the total number of points (vertices).
     * @param int $color drawing color
     * @return bool always true
     * @access public
     */
    function drawPolygon($points, $num_points, $color)
    {
        if ($num_points > 2) {
            for ($i = 0; $i<$num_points - 1; ++$i) {
                $j = $i * 2;

                $this->drawline($points[$j], $points[$j+1], $points[$j+2], $points[$j+3], $color);
            }

            $this->drawline($points[0], $points[1], $points[$num_points * 2 - 2], $points[$num_points * 2 - 1], $color);
        }

        return true;
    }

    /**
     * Destroy an image
     *
     * @return bool always true
     * @access public
     */
    function destroy()
    {
        $this->Image_XBM();
        return true;
    }

    /**
     * Sets the style to be used by all line drawing functions
     * (such as imageline() and imagepolygon()) when drawing
     * with the special color IMG_COLOR_STYLED 
     *
     * @param array $style is an array of pixels.
     * @return bool always true
     * @access public
     */
    function setStyle($style)
    {
        if (!is_array($style)) {
        	$style = array($style);
		}

        $this->_style = $style;
        return true;
    }

    /**
     * Flood fill
     *
     * @param int $x starting coordinate
     * @param int $y starting coordinate
     * @param int $color color
     * @return bool always true
     * @access public
     */
    function fill($x, $y, $color)
    {
        $color = $color ? 1 : 0;

        $stack = array(array($x, $y));

        while (sizeof($stack)) {
            list ($x, $y) = array_pop($stack);

            if ($this->getColorAt($x, $y) <> $color) {
	                $this->drawPixel($x, $y, $color);
			}

            if (($px = $this->getColorAt($sx = $x + 1, $y)) <> $color && $px !== null) {
                $stack[] = array($sx, $y);
            }

            if (($px = $this->getColorAt($x, $sy = $y + 1)) <> $color && $px !== null) {
                $stack[] = array($x, $sy);
            }

            if (($px = $this->getColorAt($sx = $x - 1, $y)) <> $color && $px !== null) {
                $stack[] = array($sx, $y);
            }

            if (($px = $this->getColorAt($x, $sy = $y - 1)) <> $color && $px !== null) {
                $stack[] = array($x, $sy);
            }
        }

        return true;
    }

    /**
     * Copy part of an image
     *
     * @param object $src_im destination Image_XBM object
     * @param int $dst_x starting x coordinate in the destination
     * @param int $dst_y starting y coordinate in the destination
     * @param int $src_x starting x coordinate in the source
     * @param int $src_y starting y coordinate in the source
     * @param int $src_w width of portion
     * @param int $src_h height of portion
     * @return bool always true
     * @access public
     */
    function copy(&$src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h)
    {
        // If pictures are equals then just copy
        if (!$dst_x && !$dst_y && $src_w == $this->_sx - 1 &&
        $src_h == $this->_sy - 1 && $src_im->_sx == $this->_sx &&
        $src_im->_sy == $this->_sy) {
            $this->_image = $src_im->_image;
            return true;
        }

        // Fast copy
        if ($src_x % 8 == $dst_x % 8) {
            $stop_x  = ($src_w >> 3) * 8;
            $start_x = $src_x % 8;

            $src_h = min($src_h, $this->_sy - $dst_y - 1, $src_im->_sy - $src_y - 1);
            $src_w = min($src_w, $this->_sx - $dst_x - 1, $src_im->_sx - $src_x - 1);

            for ($j = 0; $j < $src_h; ++$j) {
                for ($i = 0; $i < $src_w; ++$i) {
                    if ($i < $start_x || $i >= $stop_x) {
                        $this->drawPixel($dst_x + $i, $dst_y + $j, $src_im->getColorAt($src_x + $i, $src_y + $j));
                    } else {                                    
                        $this->_image[($i+$dst_x) >> 3][$dst_y + $j] = $src_im->_image[($i + $src_x) >> 3][$src_y + $j];
                        $i += 7;
                    }
                }
            }

            return true;
        }

        // Slow copy
        for ($i = 0; $i < $src_w; ++$i) {
            for ($j = 0; $j < $src_h; ++$j) {
                $this->drawPixel($dst_x + $i, $dst_y + $j, $src_im->getColorAt($src_x + $i, $src_y + $j));
            }
        }

        return true;
    }

    /**
     * Create a new image from XBM file or URL
     *
     * @param string $filename XBM file name or URL
     * @return mixed PEAR_error or true for success
     * @access public
     */
    function createFromFile($filename)
    {
        $fp = fopen($filename, 'r');
        if (!is_resource($fp)) {
            return PEAR::raiseError('Cannot open file.', 4);
        }

        for ($i = 0; $i < 2; ++$i) {
            if (feof($fp)) {
                fclose($fp);
                return PEAR::raiseError('Invalid XBM file.', 5);
            }

            $line = fgets($fp, 1024);
            if (preg_match('/^#define\s+.*?_(width|height)\s+(\d+)/', $line, $match)) {
                ${$match[1]} = $match[2];
            }
        }

        if (feof($fp) || !isset($width) || !isset($height)) {
            fclose($fp);
            return PEAR::raiseError('Invalid XBM file.', 5);
        }

        $picture = preg_replace('/^\s*static\s+(?:unsigned\s+)char\s+.*?_bits\[\]\s*=\s*\{/', '', fread($fp, @filesize($filename)));
        $picture = preg_replace('/\s*\};?\s*$/', '', $picture);

        $this->create($width, $height);
        $picture = explode(',', $picture);
        $sx = ceil($width / 8);

        for ($s = $j = 0; $j < $height; ++$j) {
            for ($i = 0; $i < $sx; ++$i) {
                $this->_image[$i][$j] = hexdec($picture[$s++]);
            }
        }

        fclose($fp);

        return true;
    }

    /**
     * Draw an ellipse
     *
     * @param int $cx center of ellipse (X coordinate)
     * @param int $cy center of ellipse (Y coordinate)
     * @param int $w width of ellipse
     * @param int $h height of ellipse
     * @param int $color color for drawing
     * @return bool always true
     * @access public
     */
    function drawEllipse($cx, $cy, $w, $h, $color)
    {
        return $this->_ellipse($cx, $cy, $w, $h, $color);
    }

    /**
     * Draw a filled ellipse
     *
     * @param int $cx center of ellipse (X coordinate)
     * @param int $cy center of ellipse (Y coordinate)
     * @param int $w width of ellipse
     * @param int $h height of ellipse
     * @param int $color color for drawing
     * @return bool always true
     * @access public
     */
    function drawFilledEllipse($cx, $cy, $w, $h, $color)
    {
        return $this->_ellipse($cx, $cy, $w, $h, $color, true);
    }

    /**
     * To draw a text string over an image using Figlet fonts
     *
     * @param string $text text for drawing
     * @param string $font font file name
     * @param mixed $size integer (font ratio) or string 'X:Y' (X size ratio and Y size ratio)
     * @param int $fgcolor foreground font color
     * @param int $bgcolor background font color
     * @param int $x start drawing from
     * @param int $y start drawing from
     * @return mixed PEAR_error or true for success
     * @access public
     */
    function drawFigletText($text, $font, $size, $fgcolor, $bgcolor, $x, $y)
    {
        if (is_numeric($size)) {
            $x_ratio = $y_ratio = $size;
        } else {
            @list($x_ratio, $y_ratio) = preg_split('/[:x]/', $size, 2);
        }

        if (!$x_ratio || !$y_ratio) {
            return PEAR::raiseError('Invalid font ratio.', 3);
        }

        require_once 'Text/Figlet.php';
        $figlet = &new Text_Figlet();

        if (PEAR::isError($error = $figlet->LoadFont($font))) {
            return PEAR::raiseError('Font loading error.', 2);
        }

        $lines = explode("\n", $figlet->lineEcho($text));
        $cnt   = sizeof($lines);

        for ($i = 0; $i < $cnt; ++$i) {
            for ($j = 0; $j < strlen($lines[$i]); ++$j) {
                $c = $lines[$i]{$j} <> ' ' ? $fgcolor : $bgcolor;

                for ($rx = 0; $rx < $x_ratio; ++$rx) {
                    for ($ry = 0; $ry < $y_ratio; ++$ry) {
                        $this->drawPixel($x + $j * $x_ratio + $rx, $y + $i * $y_ratio + $ry, $c);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Draw a ellipse
     *
     * @param int $x center of ellipse (X coordinate)
     * @param int $y center of ellipse (Y coordinate)
     * @param int $a width of ellipse
     * @param int $b height of ellipse
     * @param int $color color for drawing
     * @param bool $filled (optional) filled ellipse or not
     * @return bool always true
     * @access private
     */
    function _ellipse($x, $y, $a, $b, $color, $filled = false)
    {
        $a_square = $a * $a;
        $b_square = $b * $b;

        $row = $b;
        $col = 0;
        $two_a_square  = $a_square << 1;
        $four_a_square = $a_square << 2;
        $two_b_square  = $b_square << 1; 
        $four_b_square = $b_square << 2;

        $d = $two_a_square*(($row - 1) * $row) + $a_square + $two_b_square * (1 - $a_square);
        while ($a_square * $row > $b_square * $col) {
            if ($filled) {
                $this->drawline($col + $x, $row + $y, $col + $x, $y - $row, $color);
                $this->drawline($x - $col, $row + $y, $x - $col, $y - $row, $color);
            } else {
                $this->drawPixel($col + $x, $row + $y, $color);
                $this->drawPixel($col + $x, $y - $row, $color);
                $this->drawPixel($x - $col, $row + $y, $color);
                $this->drawPixel($x - $col, $y - $row, $color);
            }

            if ($d >= 0) {
                $row--; 
                $d -= $four_a_square*$row; 
            }

            $d += $two_b_square*(3 + ($col << 1)); 
            ++$col; 
        }

        $d = $two_b_square * ($col + 1) * $col + $two_a_square *
        ($row * ($row - 2) + 1) + (1 - $two_a_square) * $b_square;

        while ($row + 1) {
            if ($filled) {
                $this->drawline($col + $x, $row + $y, $col + $x, $y - $row, $color);
                $this->drawline($x - $col, $row + $y, $x - $col, $y - $row, $color);
            } else {
                $this->drawPixel($col + $x, $row + $y, $color);
                $this->drawPixel($col + $x, $y - $row, $color);
                $this->drawPixel($x - $col, $row + $y, $color);
                $this->drawPixel($x - $col, $y - $row, $color);
            }

            if ($d <= 0) {
                ++$col;
                $d += $four_b_square * $col;
            }

            $row--; 
            $d += $two_a_square * (3 - ($row << 1));
        }

        return true;
    }
}
?>