<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Product;
use App\Message\ImportProductsMessage;
use App\Service\CsvImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ImportProductsMessageHandler
{
    public function __construct(
        private CsvImportService $import,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(ImportProductsMessage $msg): void
    {
        foreach ($this->import->read($msg->path) as $row) {
            $p = new Product();
            $p->setName(strval($row['name'] ?? ''));
            $p->setDescription(isset($row['description']) ? strval($row['description']) : null);

            $attrs = $row['attributes'] ?? [];
            if (!is_array($attrs)) { $attrs = []; }
            $p->setAttributes($attrs);

            $this->em->persist($p);
        }
        $this->em->flush();
    }
}
