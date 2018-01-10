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

    public function createSlug($str, $delimiter = '-'){
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }

    private function trim($text){
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ',$text);
        return $text;
    }

    public function searchDir($folder){
        $pattern = '~([\\w/]\\S*?\\.[pP][dD][fF])~';
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach($files as $file) {
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }
}