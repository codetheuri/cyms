<?php

use helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var dashboard\models\ExpenseCategories[] $categories */

$this->title = 'Expense Categories';
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="block block-rounded">
            <div class="block-header block-header-default shadow-sm border-bottom border-light">
                <h3 class="block-title fw-bold">
                    <i class="fa fa-tags me-1 text-muted"></i> Manage Your dynamic Spending Categories
                </h3>
                <div class="block-options">
                    <?= Html::customButton([
                        'type' => 'modal',
                        'url' => Url::to(['category-create']),
                        'modal' => ['title' => 'Add New Spending Category', 'size' => 'md'],
                        'appearence' => ['icon' => 'plus', 'theme' => 'alt-success', 'text' => 'New Category', 'type' => 'iconText', 'size' => 'sm']
                    ]) ?>
                    <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-alt-dark ms-2 shadow-sm">
                        <i class="fa fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-vcenter">
                        <thead>
                            <tr class="fs-sm">
                                <th>Category Name</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 150px;">Status</th>
                                <th class="text-center" style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): 
                                $isTrash = ($cat->is_deleted == 1);
                            ?>
                            <tr class="<?= $isTrash ? 'bg-danger-light opacity-75' : '' ?>">
                                <td class="fw-bold text-dark <?= $isTrash ? 'text-decoration-line-through italic text-muted' : '' ?>">
                                    <?= Html::encode($cat->category_name) ?>
                                    <?php if ($isTrash): ?><small class="badge bg-danger ms-2">IN TRASH</small><?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= Html::encode($cat->description ?: 'No detail provided.') ?></td>
                                <td class="text-center">
                                    <?= $cat->recordStatus ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if (!$isTrash): ?>
                                            <?= Html::customButton([
                                                'type' => 'modal',
                                                'url' => Url::to(['category-update', 'id' => $cat->category_id]),
                                                'modal' => ['title' => 'Modify Category', 'size' => 'md'],
                                                'appearence' => ['icon' => 'pencil-alt', 'theme' => 'alt-info', 'size' => 'sm']
                                            ]) ?>
                                            
                                            <?= Html::a('<i class="fa fa-trash"></i>', ['category-trash', 'id' => $cat->category_id], [
                                                'class' => 'btn btn-sm btn-alt-danger',
                                                'data' => ['confirm' => 'Move this category to trash?', 'method' => 'post']
                                            ]) ?>
                                        <?php else: ?>
                                             <?= Html::customButton([
                                                'type' => 'modal',
                                                'url' => Url::to(['category-restore-option', 'id' => $cat->category_id]),
                                                'modal' => ['title' => 'Restore Category', 'size' => 'md'],
                                                'appearence' => ['icon' => 'undo', 'theme' => 'warning', 'size' => 'sm']
                                            ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
