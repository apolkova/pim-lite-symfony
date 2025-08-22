<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/products")]
final class ProductController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route("", methods: ["GET"])]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(Product::class)->findBy([], ["id" => "DESC"]);
        $data = array_map(function(Product $p) {
            return [
                "id" => $p->getId(),
                "name" => $p->getName(),
                "description" => $p->getDescription(),
                "attributes" => $p->getAttributes(),
                "status" => $p->getStatus(),
                "categoryId" => $p->getCategory()?->getId(),
            ];
        }, $items);

        return $this->json($data);
    }

    #[Route("", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: "[]", true, 512, JSON_THROW_ON_ERROR);

        $p = new Product();
        $p->setName((string)($data["name"] ?? ""));
        $p->setDescription(isset($data["description"]) ? (string)$data["description"] : null);
        $p->setAttributes(is_array($data["attributes"] ?? null) ? $data["attributes"] : []);
        if (isset($data["status"])) {
            $p->setStatus((string)$data["status"]);
        }
        if (isset($data["categoryId"])) {
            $cat = $this->em->getRepository(Category::class)->find((int)$data["categoryId"]);
            if ($cat) { $p->setCategory($cat); }
        }

        $this->em->persist($p);
        $this->em->flush();

        return $this->json([
            "id" => $p->getId(),
            "name" => $p->getName(),
            "description" => $p->getDescription(),
            "attributes" => $p->getAttributes(),
            "status" => $p->getStatus(),
            "categoryId" => $p->getCategory()?->getId(),
        ], 201);
    }
}