<?php

use yii\helpers\Url;

?>
<!-- Footer -->
<footer id="page-footer" class="bg-body-light">
    <div class="content py-3">
        <div class="row fs-sm">
            <div class="col-sm-6 order-sm-2 py-1 text-center text-sm-end">
                Design by <a class="fw-semibold" href="<?=Url::base(true)?>" target="_blank"><?=$_SERVER['APP_DEVELOPER']?></a>
            </div>
            <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-start">
                <a class="fw-semibold" href="<?=Url::base(true)?>" target="_blank"> &copy; <span data-toggle="year-copy"></span> <?=Yii::$app->name?> </a> 
            </div>
        </div>
    </div>
</footer>
<!-- END Footer -->