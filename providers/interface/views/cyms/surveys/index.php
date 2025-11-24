<?php

use helpers\Html;
use helpers\grid\GridView;

/** @var yii\web\View $this */
/** @var dashboard\models\search\ContainerSurveysSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Container Survey History';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-surveys-index row">
    <div class="col-md-12">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title"><?= Html::encode($this->title) ?> </h3>
          </div>
        <div class="block-content">     
            
            <div class="container-surveys-search my-3">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                // 'filterModel' => $searchModel, // Uncomment if you want column filters
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'survey_id',
                    [
                        'attribute' => 'visit_id',
                        'label' => 'Container No',
                        'value' => 'visit.container_number', // Assuming relation exists
                    ],
                    'survey_date',
                    'surveyor_name',
                    'approval_status',
                 // ... inside GridView columns ...
            [
                'class' => \helpers\grid\ActionColumn::className(),
                'template' => '{view} ', 
                'headerOptions' => ['width' => '15%'],
                'contentOptions' => ['style'=>'text-align: center;'],
                'buttons' => [
                    // VIEW BUTTON
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-eye"></i>',
                            ['view', 'survey_id' => $model->survey_id],
                            ['class' => 'btn btn-sm btn-alt-secondary', 'title' => 'View Details', 'data-pjax' => 0]
                        );
                    },
                    

                ],
            ],
            // ...
                ],
            ]); ?>
        </div>
      </div>
    </div>
</div>