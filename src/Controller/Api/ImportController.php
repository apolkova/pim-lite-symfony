<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Message\ImportProductsMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/import')]
final class ImportController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus) {}

    #[Route('', methods: ['POST'])]
    public function import(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '[]', true);
        $path = isset($data['path']) ? (string)$data['path'] : '';

        if ($path === '') {
            return $this->json(['error' => 'Missing "path" in JSON body'], 400);
        }

        // převede relativní cestu (např. "var/sample.csv") na absolutní
        $projectDir = $this->getParameter('kernel.project_dir');
        $absPath = str_starts_with($path, '/') ? $path : $projectDir . DIRECTORY_SEPARATOR . $path;

        if (!is_readable($absPath)) {
            return $this->json(['error' => sprintf('File not readable: %s', $path)], 400);
        }

        $this->bus->dispatch(new ImportProductsMessage($absPath));

        return $this->json(['status' => 'enqueued', 'path' => $path], 202);
    }

}
