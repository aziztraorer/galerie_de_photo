<?php
namespace App\Controllers;

use App\Services\CategoryService;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController
{
    public function __construct(private CategoryService $categoryService)
    {
    }

    public function list(Request $request, Response $response): Response
    {
        $categories = $this->categoryService->listCategories();
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $categories
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Invalid category id.'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $category = $this->categoryService->getCategoryById($id);
        if (!$category) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Category not found.'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $category
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function animals(Request $request, Response $response, array $args): Response
    {
        $id = (int) ($args['id'] ?? 0);
        $animals = $this->categoryService->getCategoryAnimals($id);
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $animals
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
