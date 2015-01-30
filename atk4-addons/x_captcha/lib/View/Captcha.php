<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadym
 * Date: 4/28/13
 * Time: 9:53 PM
 * To change this template use File | Settings | File Templates.
 */
namespace x_captcha;
class View_Captcha extends \View {
    public $Imagick;
    public $bg_color           = 'white';
    public $alphanum           = 'ABCDIFGHIJKLMNOPQRSTUVWXYZ1234567890'; // 'abcdefghijklmnopqrstuvwxyz1234567890'
    public $image_format       = 'png';
    public $image_width        = 150;
    public $image_height       = 40;
    public $text_length        = 6;
    public $font_size          = 30;
    public $text_position_top  = 25;
    public $text_position_left = 4;
    public $font = null;
    function init() {
        parent::init();
        $this->getImage();
    }
    function getImage() {
        $this->createImage();
        $image = $this->Imagick->getImageBlob();
        header( "Content-Type: image/{$this->Imagick->getImageFormat()}" );
        header( "Content-Length: ". $this->Imagick->getImageLength() );
        echo $image;
        exit();
    }
    private function createImage() {
        /* Create Imagick object */
        $this->Imagick = new \Imagick();

        /* Create the ImagickPixel object (used to set the background color on image) */
        $bg = new \ImagickPixel();

        /* Set the pixel color to white */
        $bg->setColor( $this->bg_color );

        /* Create a drawing object and set the font size */
        $ImagickDraw = new \ImagickDraw();

        /* Set font and font size. You can also specify /path/to/font.ttf */
        if ($this->font) $ImagickDraw->setFont($this->font);
        $ImagickDraw->setFontSize( $this->font_size );

        /* Create new empty image */
        $this->Imagick->newImage( $this->image_width, $this->image_height, $bg );

        /* Write the text on the image */
        $this->Imagick->annotateImage( $ImagickDraw, $this->text_position_left, $this->text_position_top, 0, $this->getCaptchaText() );

        /* Add some swirl */
        $this->Imagick->swirlImage( 20 );

        /* Create a few random lines */
        $ImagickDraw->line( rand( 0, $this->image_width ), rand( 0, $this->image_height ), rand( 0, $this->image_width ), rand( 0, $this->image_height ) );
        $ImagickDraw->line( rand( 0, $this->image_width ), rand( 0, $this->image_height ), rand( 0, $this->image_width ), rand( 0, $this->image_height ) );
        $ImagickDraw->line( rand( 0, $this->image_width ), rand( 0, $this->image_height ), rand( 0, $this->image_width ), rand( 0, $this->image_height ) );
        $ImagickDraw->line( rand( 0, $this->image_width ), rand( 0, $this->image_height ), rand( 0, $this->image_width ), rand( 0, $this->image_height ) );
        $ImagickDraw->line( rand( 0, $this->image_width ), rand( 0, $this->image_height ), rand( 0, $this->image_width ), rand( 0, $this->image_height ) );

        /* Draw the ImagickDraw object contents to the image. */
        $this->Imagick->drawImage( $ImagickDraw );

        /* Give the image a format */
        $this->Imagick->setImageFormat( $this->image_format );
    }
    private function getCaptchaText(){
        $string = mb_substr( $this->unicode_shuffle( $this->alphanum, strlen($this->alphanum) ), 2, $this->text_length );
        $this->controller->memorizeCaptcha($string);
        return $string;
    }

    private function unicode_shuffle($string, $chars, $format = 'UTF-8') {
        for($i=0; $i<$chars; $i++)
            $rands[$i] = rand(0, mb_strlen($string, $format));
            $s = NULL;

        foreach($rands as $r)
            $s.= mb_substr($string, $r, 1, $format);

        return $s;
    }






    function render(){
        /*
   		$this->js(true)
   			->_load('x_tags')
   			->_css('x_tags');
        */
   		return parent::render();
   	}
    function defaultTemplate() {
		// add add-on locations to pathfinder
		$l = $this->api->locate('addons',__NAMESPACE__,'location');
		$addon_location = $this->api->locate('addons',__NAMESPACE__);
		$this->api->pathfinder->addLocation($addon_location,array(
			//'js'=>'templates/js',
			//'css'=>'templates/css',
            //'template'=>'templates',
		))->setParent($l);

        //return array('view/lister/tags');
        return parent::defaultTemplate();
    }
}
