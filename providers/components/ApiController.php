<?php
namespace helpers;
use Yii;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;

class ApiController extends \yii\rest\Controller
{
    public $enableCsrfValidation = false;
    
    public function behaviors() {
        $auth     = isset(Yii::$app->params['activateAuth']) ? Yii::$app->params['activateAuth'] : FALSE;
        $origins  = isset(Yii::$app->params['allowedDomains']) ? Yii::$app->params['allowedDomains'] : "*";
        $behavior = [
            'class' => Cors::className(),
            'cors'  => [
                'Origin'                           => [$origins],
                'Access-Control-Allow-Origin'      => [$origins],  
                'Access-Control-Request-Headers'   => ['*'],         
                'Access-Control-Request-Method'    => ['POST', 'PUT', 'PATCH', 'GET', 'DELETE', 'HEAD'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age'           => 3600,                
            ],
        ]; 
        if($auth){
            $behaviors = parent::behaviors();
            unset($behaviors['authenticator']);
            $behaviors['corsFilter']=$behavior;
            $behaviors['authenticator'] = [
                'class' => HttpBearerAuth::className(),
            ];
            $behaviors['authenticator']['except'] = Yii::$app->params['safeEndpoints'];
        }else{
            $behaviors['corsFilter']=$behavior;
        }
        
        return $behaviors;
    }
    /**
     * error response
     */
    public function errorResponse($errors,$acidErrors=false,$message=false)
    {
        Yii::$app->response->statusCode = 422;
        foreach($errors as $key=>$value){
            $errors[$key]=$value[0];
        }
        if(is_array($acidErrors)){
            foreach($acidErrors['acidErrorModel'] as $key=>$value){
                foreach($value->getErrors() as $k=>$value){
                    $error[$acidErrors['errorKey']][$key][$k] = $value[0];
                }
            }
        }
        if(isset($error)){
            $errors = array_merge($errors,$error);
        }
        
        $array['errorPayload']['errors']=$errors;

        if($message){
            $array['errorPayload'] = array_merge($array['errorPayload']['errors'], $this->toastResponse(
                [
                    'statusCode'=>422,
                    'message'=>$message ? $message : 'Some data could not be validated',
                    'theme'=>'danger'
                ]
            )['toastPayload']);

        }
        return $array;
    }
    /**
     * payload response
     */
    public function payloadResponse($data,$options=[])
    {
        $options = array_merge(['statusCode'=>200,'oneRecord'=>true, 'message'=>false], $options);
        Yii::$app->response->statusCode = $options['statusCode'];
        if(!$options['oneRecord']){
            $array = [ 
                'dataPayload'=> [
                    'data'              => !empty($data->models)? $data->models : 'No records available',
                    'countOnPage'       => $data->count,
                    'totalCount'        => $data->totalCount,
                    'perPage'           => $data->pagination->pageSize,
                    'totalPages'        => $data->pagination->pageCount,
                    'currentPage'       => $data->pagination->page + 1,
                    'paginationLinks'   => $data->pagination->links,
                ]
            ];
        }else{
            $array = [ 
                'dataPayload'=> [
                    'data'              => $data,
                ]
            ];
            if($options['message']){
                $toastArray = [
                    'statusCode'=>$options['statusCode'],
                    'message'=>$options['message'],
                    'theme'=>'success',
                ];
                if(isset($options['toastOptions'])){
                    $toastArray['toastOptions'] = $options['toastOptions'];
                }
                    
                
                $array = array_merge($array, $this->toastResponse($toastArray));
            }
            //$array['dataPayload'] = $model;
        }
        return $array;
    }
    /**
     * toast response
     */
    public function toastResponse($options=[])
    {
        $options = array_merge(['statusCode'=>200,'theme'=>false, 'message'=>false], $options);
        Yii::$app->response->statusCode = $options['statusCode'];
        if(isset($options['toastOptions'])){
            $clientOptions = $options['toastOptions'];
        }else{
            $clientOptions = [];
        }
        $array = [ 
            'toastPayload'=> [
                'toastMessage'  => $options['message'] ? $options['message'] : 'Hello toast',
                'toastTheme'    => $options['theme'] ? $options['theme'] : 'info',
                'toastOptions'  => array_merge(['type'=>'sweetAlert'], $clientOptions)
            ]
        ];
        return $array;
    }
    /**
     * Query parameters cleanup
     */
    public function queryParameters($query,$modelId){   
        if(!$query){
            $data = null;
        }
        foreach($query as $key=>$value){
            if(substr($key,0,1) == '_'){
                $data[$modelId][ltrim($key,"_")]=$value;
            }else{
                $data[$key]=$value;
            }
        }
        return $data;
    }
}