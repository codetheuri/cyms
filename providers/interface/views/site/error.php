<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception$exception */

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = $name;
?>
<!-- Page Content -->
<div class="hero">
    <div class="hero-inner text-center">
        <div class="bg-body-extra-light">
            <div class="content content-full overflow-hidden">
                <div class="py-4">
                    <!-- Error Header -->
                    <h1 class="display-1 fw-bolder text-city">
                        <?= rtrim(explode('#', $name)[1], ')') ?>
                    </h1>
                    <h2 class="h4 fw-normal text-danger mb-5">
                        <?= nl2br(Html::encode($message)) ?>
                    </h2>
                    <h2 class="h4 fw-normal text-muted mb-5">
                        <p class="mb-1">
                            The above error occurred while the Web server was processing your request.
                        </p>
                        <a class="link-fx" href="<?=Url::to(['/dashboard'])?>">Go Back to Dashboard</a>
                    </h2>
                    <!-- END Error Header -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Page Content -->