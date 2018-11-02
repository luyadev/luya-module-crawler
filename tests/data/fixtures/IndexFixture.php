<?php

namespace crawlerests\data\fixtures;

use yii\test\ActiveFixture;
use luya\testsuite\fixtures\NgRestModelFixture;

class IndexFixture extends NgRestModelFixture
{
    public $modelClass = 'luya\crawler\models\Index';

    public function getData()
    {
        return [
            'index1' => [
                'url' => 'index1.php',
                'title' => 'aaa',
                'content' => 'bbb',
                'description' => 'ccc',
                'language_info' => 'en',
            ],
            'index2' => [
                'url' => 'index2.php',
                'title' => 'index2',
                'content' => 'words some other drinking words and now bug is the word barfoo',
                'description' => 'index2',
                'language_info' => 'en',
            ],
            'index3' => [
                'url' => 'index3.php',
                'title' => 'index3',
                'content' => 'stem drink stem find stem barfoo',
                'description' => 'index3',
                'language_info' => 'en',
            ],
            'index4' => [
                'url' => 'index4.php',
                'title' => 'index4',
                'content' => 'twowords',
                'description' => 'index4',
                'language_info' => 'en',
            ],

            'index7' => [
                'url' => 'index7.php',
                'title' => 'index7',
                'content' => 'item',
                'description' => 'index7',
                'language_info' => 'en',
            ],
            'index6' => [
                'url' => 'index6/else/item',
                'title' => 'index5',
                'content' => 'item',
                'description' => 'index5',
                'language_info' => 'en',
            ],
            'index5' => [
                'url' => 'index5/item',
                'title' => 'index5',
                'content' => 'item',
                'description' => 'index5',
                'language_info' => 'en',
            ],
            'index8' => [
                'url' => 'offnungszeiten.php',
                'title' => 'offnungszeiten',
                'content' => '&Ouml;ffnungszeiten &ouml;ffnungszeiten Öffnungszeiten öffnungszeiten',
                'language_info' => 'de',
            ]
        ];
    }
}
