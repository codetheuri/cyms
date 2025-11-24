<?php
use helpers\Html;
use helpers\grid\GridView;

$this->title = 'Clients & Transporters';
?>
<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-plus"></i> Add Client', ['create'], ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
    <div class="block-content">
        <div class="mb-3">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'owner_name',
                'owner_contact',
                'owner_email:email',
                'created_at:date',
                [
                    'class' => \helpers\grid\ActionColumn::class,
                    'template' => '{view} {update}',
                ],
            ],
        ]); ?>
    </div>
</div>