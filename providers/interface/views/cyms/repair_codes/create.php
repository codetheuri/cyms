<?php
$this->title = 'Add Repair Code';
$this->params['breadcrumbs'][] = ['label' => 'Master Repair Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= $this->title ?></h3>
    </div>
    <div class="block-content block-content-full">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>