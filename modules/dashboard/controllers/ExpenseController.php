<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ExpenseCategories;
use dashboard\models\Expenses;
use dashboard\models\search\ExpensesSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use helpers\models\AuditTrail;

/**
 * ExpenseController implements the CRUD actions for ExpenseCategories and Expenses models.
 */
class ExpenseController extends DashboardController
{
    /**
     * Define the permissions for this controller
     */
    public $permissions = [
        'dashboard-expense-manage' => 'Manage Yard Expenses',
        'dashboard-expense-category' => 'Manage Expense Categories',
        'dashboard-finance-reports' => 'View Financial Summary',
    ];


    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/expense');
    }
    /**
     * =========================================================================
     * EXPENSE CATEGORIES MANAGEMENT
     * =========================================================================
     */

    /**
     * List all expense categories
     */
    public function actionCategories()
    {
        Yii::$app->user->can('dashboard-expense-category');
        $categories = ExpenseCategories::find()->all(); // Show all including trash
        
        return $this->render('categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Create a Category
     */
    public function actionCategoryCreate()
    {
        Yii::$app->user->can('dashboard-expense-category');
        $model = new ExpenseCategories();
        
        if ($model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category Created Successfully.');
            return $this->redirect(['categories']);
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_category_form', [
                'model' => $model,
            ]);
        }
        else {
            return $this->render('_category_form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Update a Category
     */
    public function actionCategoryUpdate($id)
    {
        Yii::$app->user->can('dashboard-expense-category');
        $model = ExpenseCategories::findOne($id);
        if (!$model) throw new NotFoundHttpException('Category not found');
        
        if ($model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category Updated.');
            return $this->redirect(['categories']);
        }

        return $this->renderAjax('_category_form', [
            'model' => $model,
        ]);
    }

    /**
     * =========================================================================
     * MAIN EXPENSES MANAGEMENT
     * =========================================================================
     */

    /**
     * Lists all Expenses.
     */
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-expense-manage');
        $searchModel = new ExpensesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create an Expense record
     */
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-expense-manage');
        $model = new Expenses();
        $model->expense_date = date('Y-m-d');

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Expense Recorded.');
                return $this->redirect(['index']);
            }
        }

        return $this->renderAjax('_form', [
            'model' => $model,
            'categories' => ExpenseCategories::getActiveList(),
        ]);
    }

    /**
     * Update an Expense record
     */
    public function actionUpdate($id)
    {
        Yii::$app->user->can('dashboard-expense-manage');
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Expense Updated.');
                return $this->redirect(['index']);
            }
        }

        return $this->renderAjax('_form', [
            'model' => $model,
            'categories' => ExpenseCategories::getActiveList(),
        ]);
    }

    /**
     * Soft Deletes or Restores an existing Expenses model.
     */
    public function actionTrash($id)
    {
        Yii::$app->user->can('dashboard-expense-manage');
        $model = $this->findModel($id, true); // Allow finding deleted
        
        if ($model->is_deleted) {
             $model->is_deleted = 0;
             $model->status = 10; // Back to ACTIVE
             Yii::$app->session->setFlash('success', 'Expense Entry Restored.');
        } else {
             $model->is_deleted = 1;
             $model->status = 1; // DELETED in Status Trait
             Yii::$app->session->setFlash('warning', 'Expense Entry Moved to Trash.');
        }
        $model->save(false);

        return $this->redirect(['index']);
    }

    /**
     * Permanent Delete (Physically remove row)
     */
    public function actionForceDelete($id)
    {
        Yii::$app->user->can('dashboard-expense-manage');
        $model = $this->findModel($id, true);
        
        try {
            $model->delete(); // Physical delete since we aren't using behavior for expenses (usually)
            Yii::$app->session->setFlash('success', 'Registry Entry Permanently Cleared.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Critical Error: Record might be restricted by balance references.');
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Render Restore/Delete Options Modal for Expenses
     */
    public function actionRestoreOption($id)
    {
        Yii::$app->user->can('dashboard-expense-manage');
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_restore_option', [
                'model' => $this->findModel($id, true)
            ]);
        }
    }

    /**
     * =========================================================================
     * FINANCIAL SUMMARIES & REPORTS
     * =========================================================================
     */

    /**
     * Displays Profit and Loss Summary
     */
    public function actionSummary()
    {
        Yii::$app->user->can('dashboard-finance-reports');
        
        $from = $this->request->get('from', date('Y-m-01'));
        $to = $this->request->get('to', date('Y-m-t'));

        // Expenses::getTotalForPeriod automatically filters out is_deleted=1
        $summary = Expenses::getProfitLossSummary($from, $to);
        $breakdown = Expenses::getBreakdownByCategory($from, $to);

        // For Excel Export
        if ($this->request->get('export') === 'excel') {
             ob_clean();
             header("Content-type: application/vnd.ms-excel");
             header("Content-Disposition: attachment; filename=Financial_Summary_".$from."_to_".$to.".xls");
             return $this->renderPartial('summary_export', [
                 'summary' => $summary,
                 'breakdown' => $breakdown,
                 'from' => $from,
                 'to' => $to,
             ]);
        }

        return $this->render('summary', [
            'summary' => $summary,
            'breakdown' => $breakdown,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Finds the Expenses model based on its primary key value.
     */
    protected function findModel($id, $allowDeleted = false)
    {
        $condition = ['expense_id' => $id];
        if (!$allowDeleted) {
            $condition['is_deleted'] = 0;
        }
        
        if (($model = Expenses::findOne($condition)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * =========================================================================
     * CATEGORY RECOVERY ACTIONS
     * =========================================================================
     */

    public function actionCategoryTrash($id)
    {
        Yii::$app->user->can('dashboard-expense-category');
        $model = ExpenseCategories::findOne($id);
        if (!$model) throw new NotFoundHttpException('Category not found');

        if ($model->is_deleted) {
            $model->is_deleted = 0;
            $model->status = 10;
            Yii::$app->session->setFlash('success', 'Category Restored.');
        } else {
            $model->is_deleted = 1;
            $model->status = 1;
            Yii::$app->session->setFlash('warning', 'Category Moved to Trash.');
        }
        $model->save(false);
        return $this->redirect(['categories']);
    }

    public function actionCategoryForceDelete($id)
    {
        Yii::$app->user->can('dashboard-expense-category');
        $model = ExpenseCategories::findOne($id);
        if (!$model) throw new NotFoundHttpException('Category not found');

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Category Permanently Removed.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Cannot Delete: This category likely has expenses attached to it.');
        }
        return $this->redirect(['categories']);
    }

    public function actionCategoryRestoreOption($id)
    {
        Yii::$app->user->can('dashboard-expense-category');
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_category_restore_option', [
                'model' => ExpenseCategories::findOne($id)
            ]);
        }
    }
}
