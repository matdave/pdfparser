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
 * Date: 1/10/18
 * Time: 4:30 PM
 * Project: pdfparser
 */
ini_set('max_execution_time', 72000);
$corePath = $modx->getOption('pdfparser.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/pdfparser/');

$pdfparser = $modx->getService(
    'pdfparser',
    'pdfparser',
    $corePath . 'model/pdfparser/',
    array(
        'core_path' => $corePath
    )
);

$mediaSource = $modx->getOption('mediaSource',$scriptProperties, 1);
$path = $modx->getOption('path', $scriptProperties, null);
$parent = $modx->getOption('parent', $scriptProperties, 0);
$base = $modx->getOption('base_path',$scriptProperties, MODX_BASE_PATH);
$replace = $modx->getOption('replace',$scriptProperties, false);
$published = $modx->getOption('published',$scriptProperties, 1);
$searchable = $modx->getOption('searchable',$scriptProperties, 1);
$show_in_tree = $modx->getOption('show_in_tree',$scriptProperties, 1);
$hidemenu = $modx->getOption('hidemenu',$scriptProperties, 0);

$savePath = $path;

if($mediaSource > 0){
    $mediaSource = $modx->getObject('modMediaSource',$mediaSource);
    if(!empty($mediaSource) && $mediaSource instanceof modMediaSource){
        $properties = $mediaSource->getPropertyList();
        $modx->log(xPDO::LOG_LEVEL_DEBUG, 'Link PDF Media Source Properties '. $modx->toJSON($properties));
        $path = (!empty($properties['basePath'])) ? $properties['basePath'].$path : $path;
        $savePath = $path;
        if (!empty($properties['basePathRelative'])) {
            $path = $base.$path;
        }
    }else{
        $path = $base.$path;
        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Link PDF could not find media source!');
    }
}else{
    $path = $base.$path;
}
$modx->log(xPDO::LOG_LEVEL_DEBUG, 'Link PDF Path '. $path);
$realpath = realpath($path);
$modx->log(xPDO::LOG_LEVEL_DEBUG, 'Link PDF Real Path '. $realpath);
foreach($pdfparser->searchDir($realpath) as $pdf){
    if(!file_exists($pdf)){
        echo 'could not locate '. $pdf. ' <br/>';
        continue;
    }else{
        echo 'beginning process on '. $pdf. ' <br/>';
    }
    $content = str_replace($base, "", $pdf);
    $file = pathinfo($pdf);
    $alias = $pdfparser->createSlug($file['filename']);
    $modx->log(xPDO::LOG_LEVEL_DEBUG, 'Link PDF found '. $modx->toJSON($file));

    $resource = $modx->getObject('modResource', array('parent'=>$parent, 'content'=>$content));
    if(!empty($resource)) {
        if (!$replace) {
            echo 'found duplicate resource '. $resource->id. ' <br/>';
            unset($resource);
            continue;
        }
    }else{
        $resource = $modx->newObject('modResource', array('parent'=>$parent, 'content'=>$content));
    }
    $resource->set('contentType', 'application/pdf');
    $resource->set('class_key', 'modStaticResource');
    $resource->set('template',0);
    $resource->set('introtext',$pdfparser->getText($pdf));
    $resource->set('alias', $alias);
    $resource->set('pagetitle', $file['filename']);
    $resource->set('published', $published);
    $resource->set('searchable', $searchable);
    $resource->set('show_in_tree', $show_in_tree);
    $resource->set('hidemenu', $hidemenu);
    if($resource->save()){
        echo 'saved resource '. $resource->id. ' <br/>';
        $c = $modx->newQuery('modResource');
        $c->where(array('content:LIKE'=>'%'.$content.'%','parent:!='=>$parent));
        $fixLinks = $modx->getCollection('modResource',$c);
        if(!empty($fixLinks)){
            foreach($fixLinks as $r){
                $newLink = '[[~'.$resource->id.']]';
                $fixed = str_replace($content,$newLink,$r->get('content'));
                $r->set('content', $fixed);
                $r->save();
                echo 'Changed Links on  '. $r->id. ' <br/>';
                unset($r);
            }
        }
    }else{
        echo 'failed to save resource '. $resource->id. ' <br/>';
    }
    unset($resource);
    //give the server a quick break :)
    usleep(333333);
};
 