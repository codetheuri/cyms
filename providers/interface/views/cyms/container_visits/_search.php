<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Yii;

/** @var yii\web\View $this */
/** @var dashboard\models\search\ContainerVisitsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="row align-items-center mb-4">
    
    <div class="col-md-3 d-none d-md-block">
        <div class="d-flex align-items-center text-muted fs-sm">
            <span class="me-2">Show:</span>
            <?= Html::dropDownList('per-page',
                Yii::$app->request->queryParams['per-page'] ?? 25,
                Yii::$app->params['pageSize'],
                ['class' => 'form-select form-select-sm border-0 bg-body-light fw-bold', 'style' => 'width: 80px;', 'onchange' => 'this.form.submit()']
            ) ?>
        </div>
    </div>

    <div class="col-md-6">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => ['class' => 'w-100 position-relative'],
            'fieldConfig' => ['options' => ['tag' => false], 'template' => "{input}"],
        ]); ?>

        <div class="ai-glow-container">
            <div class="ai-glow-border"></div>
            
            <div class="ai-input-wrapper">
                <div class="ai-icon">
                    <i class="fa fa-sparkles fa-beat-fade" style="--fa-beat-fade-opacity: 0.4; --fa-beat-fade-scale: 1.1;"></i>
                </div>
                
                <?= $form->field($model, 'globalSearch')->textInput([
                    'class' => 'form-control ai-input',
                    'placeholder' => 'Ask the system...',
                    'autocomplete' => 'off',
                    'id' => 'ai-search-input'
                ]) ?>

                <button type="submit" class="ai-submit-btn">
                    <i class="fa fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="col-md-3"></div>
</div>

<style>
    /* 1. CONTAINER & ALIGNMENT */
    .ai-glow-container {
        position: relative;
        max-width: 600px;
        margin: 0 auto;
        border-radius: 50px;
        z-index: 1;
    }

    /* 2. THE GLOWING ANIMATED BORDER */
    .ai-glow-border {
        position: absolute;
        top: -2px; left: -2px; right: -2px; bottom: -2px;
        border-radius: 50px;
        background: linear-gradient(45deg, #ff00cc, #3333ff, #00ccff, #ff00cc);
        background-size: 400%;
        z-index: -1;
        opacity: 0; /* Hidden by default */
        transition: opacity 0.3s ease;
        filter: blur(8px); /* The Glow Effect */
    }

    /* Animate the gradient when active */
    @keyframes glowing {
        0% { background-position: 0 0; }
        50% { background-position: 400% 0; }
        100% { background-position: 0 0; }
    }

    /* 3. THE WHITE INPUT BOX */
    .ai-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: #fff; /* Solid white background */
        border-radius: 50px;
        padding: 6px 8px 6px 20px;
        border: 1px solid #e1e6e9; /* Default border */
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    /* 4. INPUT FIELD STYLING */
    .ai-input {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
        font-size: 1.1rem;
        color: #333;
        font-weight: 500;
        width: 100%;
    }
    
    .ai-input::placeholder {
        color: #999;
        font-weight: 400;
        font-style: italic;
    }

    /* 5. ICON & BUTTON */
    .ai-icon {
        font-size: 1.2rem;
        background: -webkit-linear-gradient(45deg, #3333ff, #00ccff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-right: 10px;
    }

    .ai-submit-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #3333ff 0%, #00ccff 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .ai-submit-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 0 15px rgba(0, 204, 255, 0.5);
    }

  
    .ai-glow-container:focus-within .ai-glow-border {
        opacity: 1; /* Show the glow */
        animation: glowing 10s linear infinite; /* Start moving colors */
    }

    .ai-glow-container:focus-within .ai-input-wrapper {
        border-color: transparent; /* Hide standard border */
    }

    /* Typing Animation Placeholder (Optional Polish) */
    .ai-input:focus::placeholder {
        color: transparent;
    }
</style>

<?php
$this->registerJs(<<<JS
    $('select[name="per-page"]').change(function(){
        let val = $(this).val();
        let url = new URL(window.location.href);
        url.searchParams.set('per-page', val);
        window.location.href = url.toString();
    });
JS
);
?>