<?php
namespace App\MessageHandler;

use App\Message\ScanFilesMessage;
use App\Service\DebrickedApiService;
use App\Service\NotificationService;
use App\Entity\Upload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class ScanFilesMessageHandler implements MessageHandlerInterface
{
    private DebrickedApiService $debrickedService;
    private EntityManagerInterface $em;
    private NotificationService $notificationService;

    public function __construct(DebrickedApiService $debrickedService, EntityManagerInterface $em, NotificationService $notificationService)
    {
        $this->debrickedService = $debrickedService;
        $this->em = $em;
        $this->notificationService = $notificationService;
    }

    public function __invoke(ScanFilesMessage $message)
    {
        $uploadId = $message->getUploadId();
        $upload = $this->em->getRepository(Upload::class)->find($uploadId);

        if (!$upload) {
            throw new UnrecoverableMessageHandlingException("Upload with ID $uploadId not found.");
        }

        $upload->setStatus('scanning');
        $this->em->persist($upload);
        $this->em->flush();

        try {
            // Authenticate with Debricked
            $this->debrickedService->authenticate();

            // Upload the file to Debricked
            $filePath = $this->getParameter('upload_directory') . '/' . $upload->getFileName();
            $uploadResponse = $this->debrickedService->uploadDependencyFile($filePath);

            $fileId = $uploadResponse['fileId'] ?? null;
            if (!$fileId) {
                throw new \Exception('File ID not returned from Debricked.');
            }

            // Initiate scan
            $scanResponse = $this->debrickedService->initiateScan([$fileId]);

            // Simulate retrieving scan results
            // In a real scenario, you might need to poll the API or receive a webhook
            $vulnerabilitiesFound = rand(0, 10); // Placeholder for actual scan result

            // Update upload status
            $upload->setStatus('scanned');
            $this->em->persist($upload);

            // Create ScanResult entity
            $scanResult = new ScanResult();
            $scanResult->setVulnerabilities($vulnerabilitiesFound);
            $scanResult->setScanDate(new \DateTime());
            $scanResult->setUpload($upload);

            $this->em->persist($scanResult);
            $this->em->flush();

            // Evaluate rules
            $this->evaluateRules($upload, $scanResult);
        } catch (\Exception $e) {
            $upload->setStatus('failed');
            $this->em->persist($upload);
            $this->em->flush();

            // Send failure notification
            $this->notificationService->sendEmail(
                $upload->getUser()->getEmail(),
                'Upload Failed',
                "Your upload {$upload->getFileName()} has failed."
            );

            throw new RecoverableMessageHandlingException($e->getMessage());
        }
    }

    private function evaluateRules(Upload $upload, ScanResult $scanResult): void
    {
        // Rule: Vulnerabilities found > X (e.g., X = 5)
        $threshold = 5;
        if ($scanResult->getVulnerabilities() > $threshold) {
            $message = "Upload {$upload->getFileName()} has {$scanResult->getVulnerabilities()} vulnerabilities.";

            // Send Email
            $this->notificationService->sendEmail(
                $upload->getUser()->getEmail(),
                'Vulnerabilities Detected',
                $message
            );

            // Send Slack Notification
            $slackWebhook = getenv('SLACK_WEBHOOK_URL');
            if ($slackWebhook) {
                $this->notificationService->sendSlack(
                    $slackWebhook,
                    $message
                );
            }
        }

        // Additional rules can be implemented similarly
    }
}
