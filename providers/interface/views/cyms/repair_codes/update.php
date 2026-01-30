<?php
$this->title = 'Update Repair Code: ' . $model->repair_code;
$this->params['breadcrumbs'][] = ['label' => 'Master Repair Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= $this->title ?></h3>
    </div>
    <div class="block-content block-content-full">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>