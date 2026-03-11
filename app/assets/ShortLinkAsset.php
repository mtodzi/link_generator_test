<?php

namespace app\assets;

use yii\web\AssetBundle;

class ShortLinkAsset extends AssetBundle
{
    public $sourcePath = '@app/views/site';
    public $js = [
        'short-link.js',
    ];
    public $depends = [
        'yii\web\YiiAsset', // Включает jQuery
        'yii\bootstrap5\BootstrapAsset',
    ];
}