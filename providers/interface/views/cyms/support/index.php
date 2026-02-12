<?php
use helpers\Html;
use helpers\widgets\ActiveForm;
use yii\helpers\Url;

$this->title = 'Help & Support';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        
        <div class="block block-rounded content-card mb-4 border-start border-5 border-primary shadow-sm">
            <div class="block-content block-content-full d-flex justify-content-between align-items-center py-4">
                <div>
                    <h3 class="block-title fw-bold text-dark fs-3 mb-1">
                        How can we help?
                    </h3>
                    <p class="fs-sm text-muted mb-0">
                        Submit a ticket below and our tech team will assist you.
                    </p>
                </div>
                <div class="p-3 bg-primary-light rounded-circle text-primary">
                    <i class="fa fa-life-ring fa-2x"></i>
                </div>
            </div>
        </div>

        <div class="block block-rounded content-card shadow-lg">
            <div class="block-content block-content-full p-4 p-md-5">
                
                <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">What's the issue?</label>
                        <?= $form->field($model, 'subject')->textInput([
                            'placeholder' => 'e.g. Cannot print Gate Pass',
                            'class' => 'form-control form-control-lg form-control-alt',
                            'autofocus' => true
                        ])->label(false) ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Priority Level</label>
                        <div class="row g-3">
                            <div class="col-4">
                                <label class="w-100 cursor-pointer">
                                    <input type="radio" name="DynamicModel[priority]" value="Low" class="btn-check" checked>
                                    <div class="btn btn-outline-secondary w-100 py-2 border-2">Low</div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="w-100 cursor-pointer">
                                    <input type="radio" name="DynamicModel[priority]" value="Medium" class="btn-check">
                                    <div class="btn btn-outline-warning w-100 py-2 border-2">Medium</div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="w-100 cursor-pointer">
                                    <input type="radio" name="DynamicModel[priority]" value="High" class="btn-check">
                                    <div class="btn btn-outline-danger w-100 py-2 border-2">High</div>
                                </label>
                            </div>
                        </div>
                        </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Description</label>
                    <?= $form->field($model, 'message')->textarea([
                        'rows' => 6, 
                        'placeholder' => 'Please explain the issue in detail...',
                        'class' => 'form-control form-control-alt'
                    ])->label(false) ?>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Screenshot (Optional)</label>
                    <div class="d-flex align-items-center p-2 bg-body-light border rounded">
                        <div class="me-3 ps-2 text-muted">
                            <i class="fa fa-paperclip fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <?= $form->field($model, 'attachment')->fileInput(['class' => 'form-control border-0 bg-transparent shadow-none'])->label(false) ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
                    <a href="<?= Url::to(['/dashboard/default/index']) ?>" class="btn btn-alt-secondary px-4 rounded-pill">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fa fa-paper-plane me-2"></i> Submit Ticket
                    </button>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <div class="text-center mt-3 text-muted fs-xs">
            Technical Support Team &bull; <i class="fa fa-envelope me-1"></i> support@system.com
        </div>

    </div>
</div>

<style>
    /* Styling for the Priority Buttons */
    .btn-check:checked + .btn-outline-secondary { background-color: #6c757d; color: white; }
    .btn-check:checked + .btn-outline-warning { background-color: #ffc107; color: black; }
    .btn-check:checked + .btn-outline-danger { background-color: #dc3545; color: white; }
    .cursor-pointer { cursor: pointer; }
</style>