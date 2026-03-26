<?php

use helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model helpers\models\AuditTrail */

$this->title = 'Audit Entry #' . $model->id;
?>

<div class="block block-rounded mb-0">
    <div class="block-content pb-4">
        <div class="row items-push">
            <!-- Left Column: Summary Info -->
            <div class="col-lg-4">
                <div class="block block-rounded bg-body-light h-100 mb-0 shadow-sm">
                    <div class="block-content">
                        <div class="d-flex align-items-center mb-4">
                            <div class="item item-circle bg-white text-primary shadow-sm me-3">
                                <i class="fa fa-user-shield"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">Activity Detail</h4>
                                <p class="fs-xs text-muted mb-0">Security Log ID: #<?= $model->id ?></p>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush bg-transparent">
                            <li class="list-group-item bg-transparent px-0 border-white-op">
                                <span class="d-block fs-xs fw-bold text-uppercase text-muted mb-1">Performed By</span>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs bg-primary-light text-primary fw-bold me-2">
                                        <?= strtoupper(substr($model->user->username ?? 'A', 0, 1)) ?>
                                    </div>
                                    <span class="fw-bold"><?= $model->user ? Html::encode($model->user->username) : 'Anonymous' ?></span>
                                </div>
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-white-op">
                                <span class="d-block fs-xs fw-bold text-uppercase text-muted mb-1">Time of Action</span>
                                <span class="fw-medium text-dark"><?= Yii::$app->formatter->asDatetime($model->audit_time) ?></span>
                                <div class="fs-xs text-muted mt-1"><?= Html::timeAgo($model->audit_time) ?></div>
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-white-op">
                                <span class="d-block fs-xs fw-bold text-uppercase text-muted mb-1">Category / Area</span>
                                <?php
                                $entityLabels = [
                                    'ContainerVisits' => 'Gate Operations / Visits',
                                    'ContainerSurveys' => 'Quality Surveys',
                                    'BillingRecords' => 'Finance & Invoicing',
                                    'User' => 'User Security',
                                    'Profiles' => 'User Profiles',
                                    'Role' => 'Access Roles',
                                    'Permission' => 'System Permissions',
                                    'User Session' => 'Session Activity',
                                ];
                                $cleanModel = trim(str_replace(['dashboard:', 'admin:'], '', $model->model_name));
                                $displayLabel = $entityLabels[$cleanModel] ?? $cleanModel;
                                ?>
                                <span class="badge bg-secondary px-2"><?= Html::encode($displayLabel) ?></span>
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-white-op">
                                <span class="d-block fs-xs fw-bold text-uppercase text-muted mb-1">Action Performed</span>
                                <?php
                                $opColor = match($model->operation) {
                                    'INSERT' => 'success',
                                    'UPDATE' => 'info',
                                    'DELETE' => 'danger',
                                    'LOGIN', 'LOGOUT' => 'primary',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $opColor ?> fw-bold"><?= $model->getFriendlyOperation() ?></span>
                            </li>
                            <li class="list-group-item bg-transparent px-0 border-0">
                                <span class="d-block fs-xs fw-bold text-uppercase text-muted mb-1">Access Point (IP)</span>
                                <code class="fs-xs"><?= Html::encode($model->ip_address) ?></code>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column: Data Changes -->
            <div class="col-lg-8">
                <h5 class="fw-bold border-bottom pb-2 mb-3">
                    <i class="fa fa-history me-2 text-primary"></i> 
                    Modified Information
                </h5>

                <?php
                $oldData = json_decode($model->old_value, true);
                $newData = json_decode($model->new_value, true);
                
                if (is_array($newData) && !empty($newData)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-vcenter border">
                            <thead class="bg-body-dark">
                                <tr>
                                    <th class="fs-xs text-uppercase" style="width: 30%;">Field Name</th>
                                    <th class="fs-xs text-uppercase text-danger">Previous Value</th>
                                    <th class="fs-xs text-uppercase text-success">Updated Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($newData as $field => $newValue): 
                                    $oldValue = $oldData[$field] ?? null;
                                ?>
                                    <tr>
                                        <td class="fw-bold fs-xs text-uppercase text-muted">
                                            <?= Html::encode(str_replace('_', ' ', $field)) ?>
                                        </td>
                                        <td class="text-danger fs-sm font-monospace" style="background-color: rgba(210, 108, 122, .05);">
                                            <?php if ($oldValue === null || $oldValue === ''): ?>
                                                <em class="text-muted opacity-50">Null</em>
                                            <?php else: ?>
                                                <?= Html::encode(is_array($oldValue) ? json_encode($oldValue) : $oldValue) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-success fw-bold fs-sm font-monospace" style="background-color: rgba(86, 175, 102, .05);">
                                            <?= Html::encode(is_array($newValue) ? json_encode($newValue) : $newValue) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 bg-body-light rounded">
                        <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No specific field modifications were recorded for this action.</p>
                        <div class="mt-2">
                             <span class="fw-bold text-dark"><?= Html::encode($model->old_value ?: 'N/A') ?></span> 
                             <i class="fa fa-arrow-right mx-2 text-muted"></i>
                             <span class="fw-bold text-primary"><?= Html::encode($model->new_value ?: 'N/A') ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-primary d-flex align-items-center mt-4 mb-0 py-2 fs-xs" role="alert">
                    <i class="fa fa-shield-alt me-2"></i>
                    <div>
                        Advanced system context and technical headers have been hidden for security.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
