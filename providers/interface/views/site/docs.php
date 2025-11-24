<?php

use yii\helpers\Url;

$this->registerCssFile(Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/swagger-ui.css'));
$this->registerCssFile(Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/style.css'));
?>



    <div id="swagger-ui">
    </div>
    <?php
    $this->registerJsFile(Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/bootstrap.bundle.min.js'));
    $this->registerJsFile(Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/swagger-ui-bundle.js'));
    $this->registerJsFile(Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/swagger-ui-standalone-preset.js'));

    ?>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "<?= Url::to([$_ENV['APP_VERSION'] . '/docs/openapi-json-resource.json', 'mod' => $mod]) ?>",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset.slice(1) // here
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
