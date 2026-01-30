<?php

namespace dashboard\models;

use Yii;
use yii\helpers\ArrayHelper;

class Model extends \yii\base\Model
{
    /**
     * Creates and populates a set of models.
     * Safe for new records and works with ANY table (dynamic PK).
     */
    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model    = new $modelClass;
        $formName = $model->formName();
        $post     = Yii::$app->request->post($formName);
        $models   = [];

        // 1. Get the Primary Key Name dynamically (e.g., 'damage_id', 'id', etc.)
        $pk = $model->primaryKey()[0];

        if (!empty($multipleModels)) {
            // 2. Filter out any models that don't have an ID (fixes the array_combine error)
            $multipleModels = array_filter($multipleModels, function($item) use ($pk) {
                return !empty($item->$pk);
            });

            // 3. Map remaining models by their Primary Key
            $keys = array_keys(ArrayHelper::map($multipleModels, $pk, $pk));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                // 4. Check if POST data contains the PK and matches an existing model
                if (isset($item[$pk]) && !empty($item[$pk]) && isset($multipleModels[$item[$pk]])) {
                    $models[] = $multipleModels[$item[$pk]];
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }
}