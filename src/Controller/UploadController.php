<?php
namespace App\Controller;

use App\Entity\Upload;
use App\Message\ScanFilesMessage;
use App\Service\DebrickedApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadController extends AbstractController
{
    private EntityManagerInterface $em;
    private DebrickedApiService $debrickedService;
    private MessageBusInterface $bus;

    public function __construct(EntityManagerInterface $em, DebrickedApiService $debrickedService, MessageBusInterface $bus)
    {
        $this->em = $em;
        $this->debrickedService = $debrickedService;
        $this->bus = $bus;
    }

    /**
     * @Route("/api/upload", name="api_upload", methods={"POST"})
     */
    public function upload(Request $request): JsonResponse
    {
        $files = $request->files->get('files');
        if (!$files) {
            return $this->json(['error' => 'No files uploaded.'], 400);
        }

        $user = $this->getUser(); // Assuming authentication is implemented

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $upload = new Upload();
            $upload->setFileName($file->getClientOriginalName());
            $upload->setStatus('pending');
            $upload->setUser($user);

            // Persist the upload entity
            $this->em->persist($upload);
            $this->em->flush();

            // Move the file to a directory (e.g., uploads/)
            $uploadDir = $this->getParameter('kernel.project_dir') . '/uploads';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            try {
                $file->move($uploadDir, $file->getClientOriginalName());
            } catch (FileException $e) {
                $upload->setStatus('failed');
                $this->em->persist($upload);
                $this->em->flush();
                // Optionally, trigger a notification for upload failure
                continue;
            }

            // Dispatch a message to process the scan
            $this->bus->dispatch(new ScanFilesMessage($upload->getId()));
        }

        return $this->json(['status' => 'Files uploaded successfully.'], 200);
    }
}
