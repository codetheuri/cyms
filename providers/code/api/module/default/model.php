<?php
/**
 * This is the template for generating the base model class for a module.
 */

echo "<?php\n";
?>
namespace <?= $generator->moduleID ?>\models;

/**
 * This is the base model class for <?= $generator->moduleID ?> module.
 */
class BaseModel extends \helpers\ActiveRecord
{
    
}
