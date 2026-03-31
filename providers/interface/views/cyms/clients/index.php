<?php
use helpers\Html;
use helpers\grid\GridView;
use yii\helpers\Url;

$this->title = 'Clients & Transporters';
?>
<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= Html::encode($this->title) ?></h3>
        <div class="block-options">
            
            <?=html::customButton(
                [
                    'type' => 'modal',
                    'url' => Url::to(["create"]),
                    'appearence' => [
                        'icon'=>'fa plus', 
                        'type' => 'text',
                        'text' => 'Add Client',
                        'theme' => 'primary',
                        'visible' => Yii::$app->user->can('dashboard-container-owner-create', true)
                    ],
                    'modal' => ['title' => 'New Client']
                ]
            )
            ?>
          
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