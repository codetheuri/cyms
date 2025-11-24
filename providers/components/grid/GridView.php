<?php

namespace helpers\grid;

class GridView extends \yii\grid\GridView
{
    public $layout = '{items}{summary}{pager}';
    public $filterSelector = 'select[name="per-page"]';
}
