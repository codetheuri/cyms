<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var dashboard\models\ContainerVisits $model */
/* @var array $shippingLines */
/* @var array $owners */
/* @var array $types */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'New Gate IN Entry' : 'Update Gate IN Entry';
$readOnlyAttr = ['readonly' => !$isNew];
?>

<div class="block block-rounded content-card">
    <div class="block-header block-header-default">
        <h3 class="block-title fw-bold"><?= $this->title ?></h3>
        <div class="block-options">
            <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back to List', ['index'], ['class' => 'btn btn-sm btn-alt-secondary']) ?>
        </div>
    </div>
    <div class="block-content block-content-full">
        
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <!-- 1. TICKET & CONTAINER -->
        <h5 class="text-primary border-bottom pb-2 mb-4"><i class="fa fa-ticket-alt me-2"></i> Ticket & Container</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'ticket_no_in')->textInput(['readonly' => true, 'class' => 'form-control bg-body-light']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'date_in')->input('date', $readOnlyAttr) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'time_in')->input('time', $readOnlyAttr) ?>
            </div>
            
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'container_number')->textInput(['class' => 'form-control form-control-lg fw-bold text-uppercase', 'placeholder' => 'ABCD1234567']) ?>
            </div>
            
            <!-- CONTAINER TYPE (With Quick Add) -->
            <div class="col-md-4 mb-3">
                <label class="form-label">Type / Size</label>
                <div class="input-group">
                    <?= $form->field($model, 'container_type_id', ['options' => ['tag' => false]])->dropDownList(
                        $types, 
                        ['prompt' => 'Select Type...', 'class' => 'form-select', 'id' => 'type-dropdown']
                    )->label(false) ?>
                    
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addTypeModal" title="Add New Type">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'seal_number_in')->textInput(['placeholder' => 'e.g. 123456']) ?>
            </div>
        </div>

        <!-- 2. SHIPPING & VOYAGE -->
        <h5 class="text-primary border-bottom pb-2 mb-4 mt-4"><i class="fa fa-ship me-2"></i> Shipping Details</h5>
        <div class="row">
            <!-- SHIPPING LINE (With Quick Add) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Shipping Line</label>
                <div class="input-group">
                    <?= $form->field($model, 'shipping_line_id', ['options' => ['tag' => false]])->dropDownList(
                        $shippingLines, 
                        ['prompt' => 'Select Line...', 'class' => 'form-select', 'id' => 'line-dropdown']
                    )->label(false) ?>
                    
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addLineModal" title="Add New Line">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <?= $form->field($model, 'shipping_agent_name')->textInput(['placeholder' => 'Agent Name']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'vessel_name')->textInput() ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'voyage_number')->textInput() ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'bl_number')->textInput() ?>
            </div>
        </div>
        
        <!-- 3. TRUCK & OWNER -->
        <h5 class="text-primary border-bottom pb-2 mb-4 mt-4"><i class="fa fa-truck me-2"></i> Transport Details</h5>
        <div class="row">
            <!-- OWNER (With Quick Add) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Container Owner / Transporter</label>
                <div class="input-group">
                    <?= $form->field($model, 'container_owner_id', ['options' => ['tag' => false]])->dropDownList(
                        $owners, 
                        ['prompt' => 'Select Owner...', 'class' => 'form-select', 'id' => 'owner-dropdown']
                    )->label(false) ?>
                    
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addOwnerModal" title="Add New Owner">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <?= $form->field($model, 'truck_owner_contact_in')->textInput(['placeholder' => 'Driver Contact (Optional)']) ?>
            </div>

            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'vehicle_reg_no_in')->textInput(['class' => 'form-control text-uppercase']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'trailer_reg_no_in')->textInput(['class' => 'form-control text-uppercase']) ?>
            </div>
            <div class="col-md-4 mb-3">
                <?= $form->field($model, 'truck_type_in')->dropDownList(['TR' => 'TR', 'TAST' => 'TAST'], ['prompt' => 'Select...']) ?>
            </div>
            
            <div class="col-md-6 mb-3">
                <?= $form->field($model, 'driver_name_in')->textInput() ?>
            </div>
            <div class="col-md-6 mb-3">
                <?= $form->field($model, 'driver_id_in')->textInput() ?>
            </div>
        </div>

        <!-- 4. DOCUMENTS & COMMENTS -->
        <h5 class="text-primary border-bottom pb-2 mb-4 mt-4"><i class="fa fa-paperclip me-2"></i> Evidence & Docs</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Container Photo</label>
                <?= $form->field($model, 'arrival_photo_file')->fileInput(['accept' => 'image/*'])->label(false) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Documents (ID, Manifest, etc)</label>
                <?= $form->field($model, 'document_files[]')->fileInput(['multiple' => true, 'accept' => 'image/*,application/pdf'])->label(false) ?>
            </div>
            <div class="col-md-12 mb-3">
                <?= $form->field($model, 'comments_in')->textarea(['rows' => 2, 'placeholder' => 'Any remarks...']) ?>
            </div>
        </div>

        <div class="pt-4 border-top mt-3">
            <?= Html::submitButton('<i class="fa fa-check"></i> Save Gate IN', ['class' => 'btn btn-lg btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<!-- ===================== MODALS ===================== -->

<!-- 1. ADD OWNER MODAL -->
<div class="modal fade" id="addOwnerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light"><h5 class="modal-title">Add Owner</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="form-owner">
                    <div class="mb-3"><label>Name</label><input type="text" name="MasterContainerOwners[owner_name]" class="form-control" required></div>
                    <div class="mb-3"><label>Contact</label><input type="text" name="MasterContainerOwners[owner_contact]" class="form-control"></div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. ADD SHIPPING LINE MODAL -->
<div class="modal fade" id="addLineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light"><h5 class="modal-title">Add Shipping Line</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="form-line">
                    <div class="mb-3"><label>Code</label><input type="text" name="MasterShippingLines[line_code]" class="form-control" placeholder="e.g. MSC" required></div>
                    <div class="mb-3"><label>Name</label><input type="text" name="MasterShippingLines[line_name]" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 3. ADD CONTAINER TYPE MODAL -->
<div class="modal fade" id="addTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-body-light"><h5 class="modal-title">Add Container Type</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="form-type">
                    <div class="row">
                        <div class="col-6 mb-3"><label>ISO Code</label><input type="text" name="MasterContainerTypes[iso_code]" class="form-control" placeholder="45G1" required></div>
                        <div class="col-6 mb-3"><label>Size (ft)</label><input type="number" name="MasterContainerTypes[size]" class="form-control" placeholder="40" required></div>
                    </div>
                    <div class="mb-3"><label>Group</label><input type="text" name="MasterContainerTypes[type_group]" class="form-control" placeholder="HC, GP, RE" required></div>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===================== JAVASCRIPT ===================== -->
<?php 
$urlOwner = Url::to(['/dashboard/visit/ajax-create-owner']); 
$urlLine  = Url::to(['/dashboard/shipping-line/ajax-create']); 
$urlType  = Url::to(['/dashboard/container-type/ajax-create']); 

$script = <<< JS
    function setupQuickAdd(formId, url, dropdownId, modalId) {
        $(formId).on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: url, type: 'POST', data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        $(dropdownId).append(new Option(res.name, res.id, true, true));
                        bootstrap.Modal.getInstance(document.querySelector(modalId)).hide();
                        $(formId)[0].reset();
                    } else { alert('Error saving data.'); }
                }
            });
        });
    }

    setupQuickAdd('#form-owner', '$urlOwner', '#owner-dropdown', '#addOwnerModal');
    setupQuickAdd('#form-line',  '$urlLine',  '#line-dropdown',  '#addLineModal');
    setupQuickAdd('#form-type',  '$urlType',  '#type-dropdown',  '#addTypeModal');
JS;
$this->registerJs($script);
?>