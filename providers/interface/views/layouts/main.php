<?php

use yii\helpers\Html;
use ui\bundles\MainAsset;

MainAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title === null ? Yii::$app->name : Yii::$app->name . ' - ' . $this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <div id="home-cover">
        <div class="c20 center-box text-align-center">
            <h1>Ready to begin the fun</h1>
            <p>This is just a default welcome page to <?= Yii::$app->name ?></p>
        </div>
    </div>
    <?=$content?>
    <div class="text-align-center padding-20">Running <?= Yii::$app->name ?> <?= $_ENV['APP_VERSION'] ?></div>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>