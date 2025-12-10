<?php
use yii\helpers\Html;
use yii\bootstrap5\Modal;
use yii\widgets\ActiveForm;

$this->title = 'Yard Position Map';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- <div class="block block-rounded content-card mb-3">
    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
        <div>
            <h3 class="block-title">Yard Overview</h3>
            <p class="fs-sm text-muted mb-0">
                <span class="badge bg-success me-2">Empty</span> Available for parking
                <span class="badge bg-danger ms-3 me-2">Occupied</span> Container in slot
            </p>
        </div>
        <div>
             <button class="btn btn-sm btn-alt-secondary"><i class="fa fa-print"></i> Print Map</button>
        </div>
    </div>
</div> -->
<div class="block block-rounded content-card mb-3">
    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
        <div>
            <h3 class="block-title">Yard Overview</h3>
            <p class="fs-sm text-muted mb-0">
                <span class="badge bg-success me-2">Empty</span> Available
                <span class="badge bg-danger ms-3 me-2">Occupied</span> Occupied
            </p>
        </div>
        <div>
             <a href="<?= \yii\helpers\Url::to(['create']) ?>" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> Add Slot
             </a>
             <button class="btn btn-sm btn-alt-secondary ms-1" onclick="window.print()">
                <i class="fa fa-print"></i> Print Map
             </button>
        </div>
    </div>
</div>

<div class="block block-rounded content-card">
    <ul class="nav nav-tabs nav-tabs-block" role="tablist">
        <?php $first = true; foreach ($yardMap as $block => $slots): ?>
            <li class="nav-item">
                <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= $block ?>" data-bs-toggle="tab" data-bs-target="#block-<?= $block ?>">
                    Block <?= $block ?>
                </button>
            </li>
        <?php $first = false; endforeach; ?>
    </ul>
    
    <div class="block-content tab-content">
        <?php $first = true; foreach ($yardMap as $block => $slots): ?>
            <div class="tab-pane <?= $first ? 'active' : '' ?> p-3" id="block-<?= $block ?>">
                
                <div class="row g-3">
                    <?php foreach ($slots as $slot): ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            
                            <?php if ($slot->current_visit_id): ?>
                                <div class="card h-100 border-start border-4 border-danger shadow-sm">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-bold text-dark"><?= $slot->slot_name ?></span>
                                            <a href="<?= \yii\helpers\Url::to(['unpark', 'id' => $slot->slot_id]) ?>" 
                                               class="text-danger" title="Unpark / Move" data-method="post" data-confirm="Remove container from this slot?">
                                                <i class="fa fa-times-circle"></i>
                                            </a>
                                        </div>
                                        
                                        <div class="fs-sm fw-bold text-primary text-truncate" title="<?= $slot->visit->container_number ?>">
                                            <?= $slot->visit->container_number ?>
                                        </div>
                                        <div class="fs-xs text-muted text-truncate">
                                            <?= $slot->visit->containerOwner->owner_name ?? 'Unknown' ?>
                                        </div>
                                        <div class="fs-xs mt-1">
                                            <span class="badge bg-secondary"><?= $slot->visit->shippingLine->line_code ?? '-' ?></span>
                                            <span class="badge bg-info"><?= '40HC' // Replace with dynamic type ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card h-100 border-start border-4 border-success shadow-sm bg-body-light">
                                    <div class="card-body p-2 text-center d-flex flex-column justify-content-center">
                                        <div class="fw-bold text-success mb-2"><?= $slot->slot_name ?></div>
                                        <button class="btn btn-sm btn-outline-success py-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modal-assign"
                                                data-slot-id="<?= $slot->slot_id ?>"
                                                data-slot-name="<?= $slot->slot_name ?>">
                                            <i class="fa fa-plus"></i> Park
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        <?php $first = false; endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modal-assign" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Park Container in Slot: <span id="modal-slot-name" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <?php $form = ActiveForm::begin(['action' => ['assign', 'id' => 0], 'id' => 'assign-form']); ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Container</label>
                    <?= Html::dropDownList('YardSlots[current_visit_id]', null, $containerDropdown, [
                        'class' => 'form-select form-control-lg', 
                        'prompt' => 'Choose unparked container...',
                        'required' => true
                    ]) ?>
                    <div class="form-text">Only showing containers currently 'In Yard' that are not yet assigned a position.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Position</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
// Simple script to pass Slot ID to the modal action URL
$script = <<< JS
    var modal = document.getElementById('modal-assign');
    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-slot-id');
        var name = button.getAttribute('data-slot-name');
        
        // Update Title
        document.getElementById('modal-slot-name').textContent = name;
        
        // Update Form Action URL dynamically
        var form = document.getElementById('assign-form');
        var action = form.getAttribute('action');
        // Replace the dummy 'id=0' with real ID
        form.setAttribute('action', action.replace('id=0', 'id=' + id));
    });
JS;
$this->registerJs($script);
?>