<?php

namespace admin\hooks;

use Yii;
use yii\base\Component;
use helpers\models\SystemSettings as Setup;
use yii\base\InvalidConfigException;

class Settings extends Component
{
    protected $data = array();
    protected $label;

    public function init()
    {
        $items = Setup::find()->where(['status' => 10, 'is_deleted' => 0])->all();
        foreach ($items as $item) {
            if ($item['key'])
                $this->data[$item['key']] = $item['current_value'] === '' || $item['current_value'] === null ?  $item['default_value'] : $item['current_value'];
            $this->label[$item['key']] = $item['label'];
        }
        parent::init();
    }
    public function get($key, $label = false)
    {
        $key = strtoupper($key);
        if (array_key_exists($key, $this->data))
            return ($label == false) ? $this->data[$key] : $this->label[$key];
        else
            return null;
    }
    public function set($key, $value)
    {
        $model = Setup::findOne(['key' => strtoupper($key)]);
        if (!$model)
            throw new InvalidConfigException('Undefined Key: ' . $key);
        $model->current_value = $value;
        if ($model->save(false))
            $this->data[$key] = $value;
    }
    public function add($params)
    {
        if (isset($params[0]) && is_array($params[0])) {
            foreach ($params as $item)
                $this->createParameter($item);
        } elseif ($params)
            $this->createParameter($params);
    }

    public function delete($key)
    {
        if (is_array($key)) {
            foreach ($key as $item)
                $this->removeParameter($item);
        } elseif ($key)
            $this->removeParameter($key);
    }
    protected function createParameter($param)
    {
        if (!empty($param['key'])) {
            $model = Setup::findOne(array('key' => $param['key']));
            if ($model === null) {
                $model = new Setup();
            }
            $model->key = strtoupper($param['key']);
            $model->label = isset($param['label']) ? $param['label'] : ucwords(str_replace("_", " ", $param['key']));
            $model->label = ucwords($model->label);
            $model->category = isset($param['category']) ? $param['category'] : 'GENERAL';
            $model->default_value = isset($param['default']) ? $param['default'] : '';
            $model->disposition = isset($param['disposition']) ? $param['disposition'] : false;
            $model->input_type = isset($param['input_type']) ? $param['input_type'] : 'textInput';
            $model->input_preload = isset($param['input_preload']) ? $param['input_preload'] : NULL;
            $model->save(false);
        }
    }
}
