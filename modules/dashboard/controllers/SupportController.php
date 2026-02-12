<?php

namespace dashboard\controllers;

use Yii;
use helpers\DashboardController;
use yii\base\DynamicModel;
use yii\web\UploadedFile;
use dashboard\hooks\Mail; // Import the Hook

class SupportController extends DashboardController
{
    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/support');
    }

    public function actionIndex()
    {
        $model = new DynamicModel(['subject', 'message', 'priority', 'attachment']);
        $model->addRule(['subject', 'message', 'priority'], 'required')
              ->addRule(['attachment'], 'file', ['skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf, docx']);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            $file = UploadedFile::getInstance($model, 'attachment');
            $user = Yii::$app->user->identity;

            // Define Recipients
            $emailRecipients = [
                'theurij113@gmail.com',
                // Add more emails here
            ];

            // Prepare Content
            $subject = "[Support] " . $model->subject . " - " . $model->priority;
            
            $body = "<h3>New Support Ticket</h3>";
            $body .= "<p><strong>User:</strong> " . $user->username . " </p>";
            $body .= "<p><strong>Priority:</strong> <span style='color:red'>" . $model->priority . "</span></p>";
            $body .= "<hr><p><strong>Message:</strong><br>" . nl2br($model->message) . "</p>";

            // USE THE HOOK
            $mailer = new Mail(); // Or Yii::createObject(Mail::class);
            $sent = $mailer->sendSupportTicket($emailRecipients, $subject, $body, $file);

            if ($sent) {
                Yii::$app->session->setFlash('success', 'Support request sent successfully.');
                return $this->refresh();
            } else {
                // If it fails, check runtime/logs/app.log for "Support Email Error"
                Yii::$app->session->setFlash('error', 'Failed to send email. Check error logs.');
            }
        }

        return $this->render('index', ['model' => $model]);
    }
}