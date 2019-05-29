<?php

namespace luya\crawler\admin\apis;

/**
 * Link Controller.
 *
 * File has been created with `crud/create` command.
 */
class LinkController extends \luya\admin\ngrest\base\Api
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\crawler\models\Link';
}
