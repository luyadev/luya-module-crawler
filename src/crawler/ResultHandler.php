<?php

namespace luya\crawler\crawler;

use luya\crawler\models\Builderindex;
use Nadar\Crawler\Interfaces\HandlerInterface;
use Nadar\Crawler\Result;

class ResultHandler implements HandlerInterface
{
    public function afterRun(Result $result)
    {
        $index = Builderindex::findOne(['url' => $result->url->getNormalized()]);

        if (!$index) {
            $index = new Builderindex();
            $index->url = $result->url->getNormalized();
        }

        $index->content = $result->content;
        $index->title = $result->title;
        $index->description = $result->description;
        $index->language_info = $result->language;
        $index->url_found_on_page = $result->refererUrl->getNormalized();

        $index->save();
        unset($index);
    }
}