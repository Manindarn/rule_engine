<?php
namespace App\Command;

use App\Entity\Upload;
use App\Service\DebrickedApiService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckScanStatusCommand extends Command
{
    protected static $defaultName = 'app:check-scan-status';

    private EntityManagerInterface $em;
    private DebrickedApiService $debrickedService;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $em, DebrickedApiService $debrickedService, NotificationService $notificationService)
    {
        parent::__construct();
        $this->em = $em;
        $this->debrickedService = $debrickedService;
        $this->notificationService = $notificationService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks the status of ongoing scans and updates the database accordingly.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Fetch uploads with status 'scanning'
        $uploads = $this->em->getRepository(Upload::class)->findBy(['status' => 'scanning']);

        foreach ($uploads as $upload) {
            // Implement scan status check
            // Example:
            // $scanStatus = $this->debrickedService->getScanStatus($upload->getFileId());

            // For demonstration, we'll randomly mark as scanned
            $scanStatus = 'scanned'; // Replace with actual API call

            if ($scanStatus === 'scanned') {
                // Fetch vulnerabilities
                $vulnerabilities = rand(0, 10); // Replace with actual data

                // Update scan result
                $scanResult = $upload->getScanResult();
                if (!$scanResult) {
                    $scanResult = new ScanResult();
                    $scanResult->setUpload($upload);
                }
                $scanResult->setVulnerabilities($vulnerabilities);
                $scanResult->setScanDate(new \DateTime());

                $this->em->persist($scanResult);

                // Update upload status
                $upload->setStatus('scanned');
                $this->em->persist($upload);

                // Evaluate rules
                // (Same logic as in the message handler)
                // You might want to extract the rule evaluation into a separate service

                // Example:
                if ($vulnerabilities > 5) {
                    $message = "Upload {$upload->getFileName()} has {$vulnerabilities} vulnerabilities.";

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

                $output->writeln("Upload ID {$upload->getId()} scanned.");
            }
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
