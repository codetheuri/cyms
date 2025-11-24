<?php

/** @var \yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;

$this->beginContent('@ui/views/layouts/dashboard.php'); // nest inside main layout
?>
<div class="content container-fluid">
    <div class="row">
        <div class="col-xl-3 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="page-header">
                        <div class="content-page-header">
                            <h5>Settings</h5>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-md-8">
            <div class="card">
                <div class="card-body w-100">
                    <div class="content-page-header">
                        <h5 class="setting-menu">
                            <?= Html::encode($this->params['settingsTitle'] ?? $this->title ?? 'Settings'); ?>
                        </h5>
                    </div>

                    <div class="row">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>