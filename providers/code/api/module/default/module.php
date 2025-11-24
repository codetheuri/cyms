<?php
/**
 * This is the template for generating a module class file.
 */

/** @var yii\web\View $this */
/** @var yii\gii\generators\module\Generator $generator */

$className = $generator->moduleClass;
$pos = strrpos($className, '\\');
$ns = ltrim(substr($className, 0, $pos), '\\');
$className = substr($className, $pos + 1);

echo "<?php\n";
?>
namespace <?= $generator->moduleID ?>;

/**
 * @license Apache 2.0
 */
/**
 * @OA\Info(
 *     description="API documentation for <?= $generator->moduleID ?> module",
 *     version="1.0.0",
 *     title="<?= $generator->moduleID ?> Module",
 *     @OA\Contact(
 *         email="admin@crackit.co.ke",
 *         name="Ananda Douglas"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */ 

class <?= $className ?> extends \helpers\ApiModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = '<?= $generator->getControllerNamespace() ?>';
    public $name = '<?= $generator->moduleID ?>';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

    }
}

/**
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * )
 */

/**
 * @OA\OpenApi(
 *   security={
 *      {
 *          "bearerAuth":{
 *
 *          }
 *      }
 *   }
 * )
 */

/**
 * @OA\Tag(
 *     name="<?=strtoupper($generator->moduleID) ?>",
 *     description="Endpoints for the <?= strtoupper($generator->moduleID) ?> module"
 * )
 */

/**
 * @OA\Get(path="/about",
 *   summary="Module Info. ",
 *   tags={"<?= strtoupper($generator->moduleID) ?>"},
 *   security={{}},
 *   @OA\Response(
 *     response=200,
 *     description="success",
 *      @OA\JsonContent(
 *          @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/About")),
 *          
 *      )
 *   ),
 * )
 */
/**
 *@OA\Schema(
 *  schema="About",
 *  @OA\Property(property="id", type="string",title="Module ID", example="<?= strtoupper($generator->moduleID) ?>"),
 *  @OA\Property(property="name", type="string",title="Module Name", example="<?= strtoupper($generator->moduleID) ?> Module"),
 * )
 */