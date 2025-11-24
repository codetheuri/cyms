<?php

namespace helpers\traits;


trait Status
{
    public static function badge($code)
    {
        switch ($code) {
            case 1:
                $status = ['label' => 'DELETED', 'theme' => 'danger',];
                break;
            case 9:
                $status = ['label' => 'INACTIVE', 'theme' => 'warning',];
                break;
            case 10:
                $status = ['label' => 'ACTIVE', 'theme' => 'success',];
                break;

            default:
                $status = ['label' => 'Unknown', 'theme' => 'dark',];
        }
        return '<span class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-' . $status['theme'] . '-light text-' . $status['theme'] . '">' . $status['label'] . '</span>';
    }
}
