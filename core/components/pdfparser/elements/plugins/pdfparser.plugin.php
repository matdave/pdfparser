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
 * Time: 10:54 AM
 * Project: pdfparser
 */
$corePath = $modx->getOption('pdfparser.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/pdfparser/');
/** @var pdfparser $pdfparser */
$pdfparser = $modx->getService(
    'pdfparser',
    'pdfparser',
    $corePath . 'model/pdfparser/',
    array(
        'core_path' => $corePath
    )
);

if (!($pdfparser instanceof pdfparser))
    return;

switch ($modx->event->name) {

    case 'OnDocFormSave':
        if(!empty($resource)){
            $class = $resource->get('class_key');
            $contentType = $resource->get('contentType');
            $content = $resource->get('content');
            if($class == 'modStaticResource'){
                $file = realpath ($modx->getOption('base_path').$content);
                if(substr($file, -4) == '.pdf'){
                    if($contentType != 'application/pdf'){
                        $resource->set('contentType', 'application/pdf');
                        $modx->log(xPDO::LOG_LEVEL_ERROR, "pdfparser - Changed  contentType for $file");
                    }
                    $resource->set('template',0);
                    if(file_exists($file)){
                        $text = $pdfparser->getText($file);
                        $resource->set('introtext',$text);
                    }else{
                        $modx->log(xPDO::LOG_LEVEL_ERROR, "pdfparser - Coudln't find file at $file");
                    }
                    if(!$resource->save()){
                        $modx->log(xPDO::LOG_LEVEL_ERROR, "pdfparser - Could not save $resource->id");
                    }
                }
            }
        }
        break;
}
return;