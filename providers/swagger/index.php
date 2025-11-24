<?php

use yii\helpers\Url;

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title><?= Yii::$app->name ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/bootstrap.min.css') ?>" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/swagger-ui.css') ?>" />
  <link rel="stylesheet" type="text/css" href="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/style.css') ?>" />
  <link rel="icon" type="image/png" href="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/favicon-32x32.png') ?>" sizes="32x32" />
</head>

<body class="swagger-section">
  <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#"><?= Yii::$app->name ?></a>
    <div class="navbar-nav topbar">
      <div class="nav-item text-nowrap">
        <a class="nav-link px-3" href="#">...</a>
      </div>
    </div>
  </header>
  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column mb-2">
            <li class="nav-item">
              <a class="nav-link" href="<?= Url::to(['index', 'mod' => 'dashboard']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text" aria-hidden="true">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                  <line x1="16" y1="13" x2="8" y2="13"></line>
                  <line x1="16" y1="17" x2="8" y2="17"></line>
                  <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Home
              </a>
              <?php
              foreach (new DirectoryIterator(Yii::getAlias('@modules')) as $index => $fileinfo) {
                if ($fileinfo->isDir() && !$fileinfo->isDot() && $fileinfo->getFilename() != 'dashboard') {
              ?>
                  <a class="nav-link" href="<?= Url::to(['index', 'mod' => $fileinfo->getFilename()]) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text" aria-hidden="true">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                      <polyline points="14 2 14 8 20 8"></polyline>
                      <line x1="16" y1="13" x2="8" y2="13"></line>
                      <line x1="16" y1="17" x2="8" y2="17"></line>
                      <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <?= Yii::$app->getModule($fileinfo->getFilename())->name ?>
                  </a><?php
                    }
                  }
                      ?>
            </li>
          </ul>
        </div>
      </nav>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="swagger-ui">

      </main>
    </div>
  </div>

  <script src="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/swagger-ui-bundle.js') ?>" charset="UTF-8"> </script>
  <script src="<?= Yii::$app->urlManager->createAbsoluteUrl('/providers/swagger/swagger-ui-standalone-preset.js') ?>" charset="UTF-8"> </script>
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
</body>

</html>

