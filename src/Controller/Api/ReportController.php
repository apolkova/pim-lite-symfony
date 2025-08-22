<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/reports")]
final class ReportController extends AbstractController
{
    public function __construct(private Connection $conn) {}

    #[Route("/top-categories", methods: ["GET"])]
    public function topCategories(): JsonResponse
    {
        $sql = <<<SQL
            SELECT c.id, c.name, COUNT(p.id) AS cnt
            FROM category c
            LEFT JOIN product p ON p.category_id = c.id
            GROUP BY c.id, c.name
            ORDER BY cnt DESC, c.name ASC
        SQL;

        $rows = $this->conn->executeQuery($sql)->fetchAllAssociative();
        return $this->json($rows);
    }
}