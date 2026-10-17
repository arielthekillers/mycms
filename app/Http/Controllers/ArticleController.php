<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\ArticleManager;
use App\Core\Auth\AuthManager;
use App\Core\Request;
use App\Core\Response;
use App\Theme\ThemeEngine;
use RuntimeException;

final class ArticleController
{
    public function __construct(
        private ArticleManager $articles,
        private AuthManager $auth,
        private ?ThemeEngine $theme = null
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $filters = array_filter([
            'status' => $request->query('status'),
            'category_id' => $request->query('category_id'),
            'search' => $request->query('search'),
        ]);

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $limit = (int) $request->query('limit', 20);
        $offset = (int) $request->query('offset', 0);

        return $response->json([
            'data' => $this->articles->list($filters, $sortBy, $sortDir, $limit, $offset),
            'total' => $this->articles->countFiltered($filters),
            'counts' => $this->articles->counts()
        ]);
    }

    public function show(Request $request, Response $response): Response
    {
        $article = $this->articles->find((int) $request->param('id'));

        if ($article === null) {
            return $response->json(['message' => 'Article not found.'], 404);
        }

        return $response->json(['data' => $article]);
    }

    /** Public read — only published articles are visible, resolved by slug. */
    public function showPublic(Request $request, Response $response): Response
    {
        $isPreview = $request->query('preview') === '1';
        $article = $this->articles->findBySlug((string) $request->param('slug'), publishedOnly: !$isPreview);

        if ($article === null) {
            return $this->theme !== null
                ? $response->status(404)->html($this->theme->render('404'))
                : $response->status(404)->html('404 Not Found');
        }

        if ($this->theme === null) {
            return $response->json(['data' => $article]); // theme not wired — fall back to JSON
        }

        return $response->html($this->theme->render('single-article', [
            'article' => $article,
            'meta' => [
                'title' => !empty($article['meta_title']) ? $article['meta_title'] : $article['title'],
                'description' => !empty($article['meta_description']) ? $article['meta_description'] : (!empty($article['excerpt']) ? $article['excerpt'] : mb_substr(strip_tags($article['content'] ?? ''), 0, 150)),
            ],
        ]));
    }

    public function store(Request $request, Response $response): Response
    {
        $payload = $request->isJson() ? $request->json() : $request->all();

        try {
            $article = $this->articles->create($payload, $this->auth->id());
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $article], 201);
    }

    public function update(Request $request, Response $response): Response
    {
        $payload = $request->isJson() ? $request->json() : $request->all();

        try {
            $article = $this->articles->update((int) $request->param('id'), $payload);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $article]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        if ($request->query('force') === '1') {
            $this->articles->forceDelete((int) $request->param('id'));
        } else {
            $this->articles->delete((int) $request->param('id'));
        }

        return $response->json(['message' => 'Deleted.']);
    }

    public function restore(Request $request, Response $response): Response
    {
        $article = $this->articles->restore((int) $request->param('id'));

        return $response->json(['data' => $article]);
    }

    public function publish(Request $request, Response $response): Response
    {
        $article = $this->articles->publish((int) $request->param('id'));

        return $response->json(['data' => $article]);
    }

    public function unpublish(Request $request, Response $response): Response
    {
        $article = $this->articles->unpublish((int) $request->param('id'));

        return $response->json(['data' => $article]);
    }

    public function revisions(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->articles->revisionsFor((int) $request->param('id'))]);
    }

    public function restoreRevision(Request $request, Response $response): Response
    {
        $payload = $request->isJson() ? $request->json() : $request->all();
        $revisionId = (int) ($payload['revision_id'] ?? 0);
        
        $article = $this->articles->restoreRevision((int) $request->param('id'), $revisionId);
        return $response->json(['data' => $article, 'message' => 'Revisi berhasil dipulihkan.']);
    }

    public function authors(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->articles->authors()]);
    }
}
