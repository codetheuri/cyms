<?php

?>
<div class="content-block content-width overflow-auto text-align-justify">
    <div class="margin-bottom-40 overflow-auto">
        <div class="c22 grid-columns">
            <h2>Getting started</h2>
            <p>If you are seeing this then <?= Yii::$app->name ?> is ready to rock. </p>
            <ul class="tabbed-content-list table-li margin-top-20">
                <li><span class="c6 display-table-cell">PHP Version</span> <span class="display-table-cell"><?= PHP_VERSION ?></span></li>
                <li><span class="c6 display-table-cell">Server</span> <span class="display-table-cell"><?= gethostname() . ' ' . PHP_OS . '/' . PHP_SAPI ?></span></li>
                <li><span class="c6 display-table-cell">PDO</span> <span class="display-table-cell"><?= !extension_loaded('PDO') ? 'Disabled' : 'Enabled' ?></span></li>
                <li><span class="c6 display-table-cell">PDO MYSQL</span> <span class="display-table-cell"><?= !extension_loaded('pdo_mysql') ? 'Disabled' : 'Enabled' ?></span></li>
                <li><span class="c6 display-table-cell">PDO PGSQL</span> <span class="display-table-cell"><?= !extension_loaded('pdo_pgsql') ? 'Disabled' : 'Enabled' ?></span></li>
                <li><span class="c6 display-table-cell">Mbstring</span> <span class="display-table-cell"><?= !extension_loaded('mbstring') ? 'Disabled' : 'Enabled' ?></span></li>

            </ul>
        </div>
    </div>
</div>