<?php

namespace helpers;

class Html extends \yii\helpers\BaseHtml
{
    public static function customButton($param)
    {
        return  static::bS4($param);
    }
    protected static function bS4($param, $options = [], $button = null)
    {
        $param = static::initOptions($param);
        if (!$param['appearence']['visible']){
            return false;
        }
        if ($param['appearence']['type'] == 'icon') {
            $name = '<i class="fa fa-fw fa-' . $param['appearence']['icon'] . '"></i>';
        } elseif ($param['appearence']['type'] == 'text') {
            $name = $param['appearence']['text'];
        } elseif ($param['appearence']['type'] == 'iconText') {
            $name = '<i class="fa fa-fw fa-' . $param['appearence']['icon'] . '"></i> ' . $param['appearence']['text'];
        }
        if ($param['type'] == 'modal') {
            $options =
                [
                    'class' => 'btn btn-' . $param['appearence']['size'] . ' btn-' . $param['appearence']['theme'] . ' loadModal',
                    'data-payload' => $param['url'],
                    'data-size' => 'modal-' . $param['modal']['size'],
                    'data-title' => $param['modal']['title'],
                    'data-toggle' => 'modal',
                    'data-target' => '#modalUI',
                ];
            $button = static::button($name, $options);
        } elseif ($param['type'] == 'link') {
            if (array_key_exists("data", $param['appearence'])) {
                $options = [
                    'data' => [
                        'confirm' => $param['appearence']['data']['message'],
                    ],
                ];
            }
            $button = static::a('<span class="btn btn-' . $param['appearence']['size'] . ' btn-' . $param['appearence']['theme'] . '">' . $name . '</span>', $param['url'], $options);
        }
            return $button;
        
    }

    protected static function initOptions($clientOptions = [])
    {
        $options = [
            'modal' => [
                'title' => 'Modal Title',
                'size' => 'md'
            ],
            'appearence' => [
                'type' => 'icon',
                'icon' => 'exclamation-triangle',
                'theme' => 'primary',
                'size' => 'sm',
                'visible' => true,
            ]
        ];
        return (array_replace_recursive($options, $clientOptions));
    }
    public static function createPreview($text, $limit = 50)
    {
        $text = preg_replace('/\[\/?(?:b|i|u|s|center|quote|url|ul|ol|list|li|\*|code|table|tr|th|td|youtube|gvideo|(?:(?:size|color|quote|name|url|img)[^\]]*))\]/', '', $text);

        if (strlen($text) > $limit) return substr($text, 0, $limit) . "...";
        return nl2br($text);
    }
    public static function weekDates($week, $year = false, $format = 'd/m/Y')
    {
        if ($year === false) {
            $year = date('Y');
        }
        $dto = new \DateTime();
        $dto->setISODate($year, $week);
        $x = 1;
        do {
            $ret['day_' . $x] = $dto->format($format);
            $dto->modify('+1 days');
            $x++;
        } while ($x <= 7);
        return $ret;
    }
}
