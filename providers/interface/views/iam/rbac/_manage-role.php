<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;
$context = $this->context;
$labels = $context->labels();

YiiAsset::register($this);
$opts = Json::htmlEncode([
    'items' => $model->getItems(),
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
?>
<div class="auth-item-view">
    <div class="row">
        <table class="table table-hover table-vcenter">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45%;">
                        <input class="form-control search" data-target="available" placeholder="<?= 'Search for available'; ?>">
                    </th>
                    <th style="width: 5%;"></th>
                    <th class="text-center" style="width: 45%;">
                        <input class="form-control search" data-target="assigned" placeholder="<?= 'Search for assigned'; ?>">
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th class="text-center" scope="row">
                        <!-- <select multiple size="20" class="form-control list" data-target="available"></select> -->
                        <select class="form-select list"  size="15" multiple="" data-target="available">
                           
                        </select>
                    </th>
                    <td>
                        <br><br>

                        <?=
                        Html::a('<i class="fa fa-angles-right"></i>', ['assign', 'id' => $model->name], [
                            'class' => 'btn btn-success btn-assign',
                            'data-target' => 'available',
                        ]);
                        ?><br><br>
                        <?=
                        Html::a('<i class="fa fa-angles-left"></i>', ['remove', 'id' => $model->name], [
                            'class' => 'btn btn-danger btn-assign',
                            'data-target' => 'assigned',
                        ]);
                        ?>
                    </td>
                    <td class="text-center">
                        <select multiple="" size="15" class="form-select list" data-target="assigned"></select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>