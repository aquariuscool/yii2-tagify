<?php

namespace saintxak\tagify\assets;
use yii\web\AssetBundle;

class TagifyAsset extends AssetBundle
{
    public $sourcePath = '@npm/yaireo--tagify/dist';
    public $css = [
        'tagify.css',
    ];
    public $js = [
        'jQuery.tagify.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}