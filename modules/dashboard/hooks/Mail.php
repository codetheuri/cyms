<?php
namespace dashboard\hooks;
use yii\web\UploadedFile;
use Yii;


class Mail extends \yii\symfonymailer\Mailer
{
    public $useFileTransport = false;
    public $_transport;
    
    public function init()
    {
    //     $this->setTransport([
    //         'scheme' => 'smtps',
    //         'host' => 'smtp.gmail.com',
    //         'username' => 'theurij113@gmail.com',
    //         'password' => 'fnpu jbeu xumu idwo',
    //         'port' => 465,
    //         'encryption' => 'ssl',
    //     ]);
            $this->setTransport([
            'scheme' => 'smtps',
            'host' => Yii::$app->config->get('smtp_host') ?? 'smtp.gmail.com',
            'username' => Yii::$app->config->get('smtp_user'),
            'password' => Yii::$app->config->get('smtp_password'),
            'port' => (int) Yii::$app->config->get('smtp_port'),
            'encryption' => Yii::$app->config->get('email_encryption'),
        ]);
        parent::init();
    }
    
    public function sendReportAttachment($to, $subject, $body, $attachmentContent, $fileName)
    {
        try {
            $message = $this->compose()
                 ->setFrom([Yii::$app->config->get('sender_email')=> Yii::$app->name])
                ->setTo($to)
                ->setSubject($subject)
                ->setTextBody($body); // Simple text body

            // Attach the content as a file
            $message->attachContent($attachmentContent, [
                'fileName' => $fileName,
                'contentType' => 'application/vnd.ms-excel'
            ]);

            return $message->send();

        } catch (\Exception $e) {
            Yii::error("Email Error: " . $e->getMessage());
            return false;
        }
    }
   
    protected function sendEmail($to, $subject, $template, $data)
    {
        try {
           
            return $this->compose($template, ['data' => $data])
                ->setTo($to)
                ->setSubject($subject)
                ->send();

        } catch (\Exception $e) {
      
            return false;
        }
    }
    public function sendSupportTicket($to, $subject, $htmlBody, $attachment = null)
    {
        try {
            $senderEmail = Yii::$app->config->get('sender_email') ?: Yii::$app->config->get('smtp_user');
            $senderName  = Yii::$app->name . ' Support';

            $message = $this->compose()
                ->setFrom([$senderEmail => $senderName])
                ->setTo($to)
                ->setSubject($subject)
                ->setHtmlBody($htmlBody);

            // Handle Attachment
            if ($attachment instanceof UploadedFile) {
                $message->attach($attachment->tempName, [
                    'fileName' => $attachment->name,
                    'contentType' => $attachment->type
                ]);
            }

            return $message->send();

        } catch (\Exception $e) {
            Yii::error("Support Email Error: " . $e->getMessage());
            return false;
        }
    }
}