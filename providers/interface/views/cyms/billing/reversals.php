<?php

use yii\helpers\Html;
use helpers\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Audit Log: Invoice Reversals';
?>

<div class="bg-body-light border-bottom mb-4">
    <div class="content py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-grow-1 fs-3 fw-bold my-2 my-sm-3">
                <i class="fa fa-shield-alt text-danger me-2"></i> Invoice Reversals
            </h1>
            <nav class="flex-shrink-0 my-2 my-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Billing & Finance</li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="block block-rounded shadow-sm">
    <div class="block-header block-header-default bg-danger-light">
        <h3 class="block-title text-danger fw-bold">Voided & Re-Billed Invoices</h3>
    </div>
    <div class="block-content">
        <div class="alert alert-info py-2 fs-sm">
            <i class="fa fa-info-circle me-1"></i> This page serves as an immutable audit trail for all finalized invoices that were reversed and modified. It tracks the original totals and the negative payment offsets.
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => '',
            'emptyText' => '<div class="text-center text-muted py-4"><i class="fa fa-check-circle fa-3x text-success mb-2"></i><br>No reversed invoices found.</div>',
            'tableOptions' => ['class' => 'table table-striped table-vcenter table-hover mt-3'],
            'columns' => [
                [
                    'attribute' => 'created_at',
                    'label' => 'Date Reversed',
                    'format' => 'datetime',
                    'contentOptions' => ['class' => 'fw-medium'],
                ],
                [
                    'label' => 'Voided Invoice #',
                    'format' => 'raw',
                    'value' => function ($m) {
                        return '<span class="text-decoration-line-through text-muted fw-bold">' . Html::encode($m->old_invoice_number) . '</span>';
                    }
                ],
                [
                    'label' => 'New Invoice Link',
                    'format' => 'raw',
                    'value' => function ($m) {
                        // Link back to the newly generated invoice using the relation
                        if ($m->bill) {
                            return Html::a('<i class="fa fa-link"></i> ' . Html::encode($m->bill->invoice_number), ['/dashboard/billing/view', 'id' => $m->bill_id], ['class' => 'fw-bold text-primary']);
                        }
                        return '<span class="text-muted">N/A</span>';
                    }
                ],
                [
                    'label' => 'Original Total',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-end font-monospace'],
                    'value' => function ($m) {
                        return Html::encode($m->old_currency) . ' ' . number_format($m->old_grand_total, 2);
                    }
                ],
                [
                    'attribute' => 'reversal_reason',
                    'label' => 'Reason',
                    'contentOptions' => ['class' => 'text-danger fst-italic', 'style' => 'max-width: 250px; white-space: normal;'],
                ],
                [
                    'attribute' => 'reversed_by',
                    'label' => 'Authorized By',
                    'format' => 'raw',
                    'value' => function ($m) {
                        return '<span class="badge bg-dark">' . Html::encode($m->reverser->username ?? 'Admin') . '</span>';
                    }
                ],
            ],
        ]); ?>
    </div>
</div>