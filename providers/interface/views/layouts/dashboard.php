<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use ui\bundles\DashboardAsset;

DashboardAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title === null ? Yii::$app->name : Yii::$app->name . ' - ' . $this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <div id="page-container" class="sidebar-o sidebar-dark page-header-dark enable-page-overlay side-scroll page-header-fixed">
        <?= $this->render('sections/_sideBar') ?>
        <?= $this->render('sections/_header.php') ?>
        <main id="main-container">
            <div class="content ">
                <?= \helpers\widgets\swal\Alert::widget() ?>
                <?= $content ?>
            </div>
        </main>
        <div class="modal" data-bs-backdrop="static" id="modal-block-vcenter" tabindex="-1" role="dialog" aria-labelledby="modal-block-vcenter" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 id="modalHeader" class="block-title"><?= Yii::$app->name ?></h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div id="modalContent" class="block-content fs-sm">
                            <div style="text-align:center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?= $this->render('sections/_footer') ?>
    </div>
    <?php
    $this->registerJs("
        $(function(){
            $('.loadModal').click(function(){
                $('#modal-block-vcenter').modal('show')
                    .find('#modalContent')
                    .load($(this).attr('data-payload'))
                    document.getElementById('modalHeader').innerHTML =  $(this).attr('data-title')  ;
                    $('.modal-dialog').addClass($(this).attr('data-size') );
            });
        });
        yii.confirm = function (message, okCallback, cancelCallback) {
            Swal.fire({
                title:'<h4>' + message + '</h4>',
                icon: 'warning',
                showCancelButton: true,
                closeOnConfirm: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed',
            }).then((result) => {
                if (result.isConfirmed) {
                    okCallback();
                }
            })
        };
    ");

    ?>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>