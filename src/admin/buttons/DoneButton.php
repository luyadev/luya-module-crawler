<?php

namespace luya\crawler\admin\buttons;

use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;
use luya\crawler\models\Link;
use luya\crawler\admin\Module;


class DoneButton extends ActiveButton
{
    public function getDefaultLabel()
    {
        return Module::t('done_button_label');
    }  

    public function getDefaultIcon()
    {
        return 'done';
    }

    public function handle(NgRestModel $model)
    {
        if (Link::deleteAll(['url' => $model->url])) {
            $this->sendReloadEvent();
            return $this->sendSuccess(Module::t('done_button_success'));
        }

        return $this->sendError("Error while removing the Link object.");
    }
}