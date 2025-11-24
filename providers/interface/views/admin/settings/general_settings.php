<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use admin\models\static\General; 

/** @var yii\web\View $this */
/** @var General $model */

$this->title = Html::encode('General Company Settings');

// Use site_logo for PREVIEW
$logo_url = $model->site_logo ? Yii::getAlias('@web') . '/' . $model->site_logo : Yii::getAlias('@web') . '/assets/img/placeholder-logo.png';
?>

<div class="block block-rounded">
    <div class="block-header block-header-default">
        <h3 class="block-title"><?= $this->title ?></h3>
    </div>
    <div class="block-content block-content-full">
        
        <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data'],
            'fieldConfig' => [
                'template' => "<div class='input-block mb-3'>{label}\n{input}\n{hint}\n{error}</div>",
            ]
        ]); ?>

        <div class="row">
            
            <!-- SECTION 1: COMPANY INFO -->
            <div class="col-lg-12">
                <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa fa-building me-2"></i> Company Identity</h5>
            </div>
            
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'organization_name')->textInput(['placeholder' => 'Enter Company Name']) ?>
            </div>
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'organization_initials')->textInput(['placeholder' => 'Short name for reports']) ?>
            </div>
            
            <!-- Logo Upload Section -->
            <div class="col-lg-12 col-12">
                <label class="form-label fw-bold">Company Logo</label>
                <div class="input-block service-upload logo-upload mb-0 border rounded p-3 bg-body-light">
                    <div class="d-flex align-items-center mb-3">
                        <!-- Preview uses site_logo (path) -->
                        <img src="<?= $logo_url ?>" alt="Current Logo" style="max-height: 80px; border-radius: 5px;">
                        <p class="text-muted ms-3 mb-0">Max 800x400px. Click or Drag to replace.</p>
                    </div>
                    
                    <!-- Input uses logo_file (file object) -->
                    <?= $form->field($model, 'logo_file')->fileInput(['class' => 'form-control'])->label(false) ?>
                </div>
            </div>

            <!-- SECTION 2: CONTACTS -->
            <div class="col-lg-12 mt-4">
                <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa fa-phone me-2"></i> Contact & Address</h5>
            </div>
            
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'physical_address')->textInput(['placeholder' => 'Mombasa, Kenya']) ?>
            </div>
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'country')->textInput(['placeholder' => 'Kenya']) ?>
            </div>
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'primary_mobile_number')->textInput(['placeholder' => '0700000000']) ?>
            </div>
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'email_address')->textInput(['placeholder' => 'info@depotsystem.com']) ?>
            </div>
            <div class="col-lg-6 col-12">
                <?= $form->field($model, 'website')->textInput(['placeholder' => 'https://depotsystem.com']) ?>
            </div>

            <div class="col-lg-12 pt-4 border-top">
                <div class="btn-path text-end">
                    <?= Html::submitButton('<i class="fa fa-save me-1"></i> Save Changes', ['class' => 'btn btn-primary btn-lg']) ?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>