<?php
/**
 * Created by PhpStorm.
 * User: matdave
 *   ___      ___       __  ___________  ________       __  ___      ___  _______
 * |"  \    /"  |     /""\("     _   ")|"      "\     /""\|"  \    /"  |/"     "|
 *  \   \  //   |    /    \)__/  \\__/ (.  ___  :)   /    \\   \  //  /(: ______)
 *  /\\  \/.    |   /' /\  \  \\_ /    |: \   ) ||  /' /\  \\\  \/. ./  \/    |
 * |: \.        |  //  __'  \ |.  |    (| (___\ || //  __'  \\.    //   // ___)_
 * |.  \    /:  | /   /  \\  \\:  |    |:       :)/   /  \\  \\\   /   (:      "|
 * |___|\__/|___|(___/    \___)\__|    (________/(___/    \___)\__/     \_______)
 *
 * Email: mat@matdave.com
 * Twitter: @matjones
 * Date: 11/17/17
 * Time: 10:56 AM
 * Project: pdfparser
 */

require (dirname(dirname(__DIR__)).'/vendor/autoload.php');

use \Smalot\PdfParser\Parser as Parser;

class pdfparser
{
    public $config = array();

    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('pdfparser.core_path', $config, $this->modx->getOption('core_path') . 'components/pdfparser/');
        $this->config = array_merge(array(
            'basePath' => $this->modx->getOption('base_path'),
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'pluginPath' => $corePath . 'elements/plugin/',
        ), $config);
        $this->modx->addPackage('pdfparser', $this->config['modelPath']);

    }

    public function getText($filename){
        $parser = new Parser();
        $pdf = $parser->parseFile($filename);
        $text = $pdf->getText();
        $text = $this->trim($text);
        return $text;
    }

    private function trim($text){
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ',$text);
        return $text;
    }
}