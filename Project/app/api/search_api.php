<?php
// 위키피디아 검색 API
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!current_user()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = trim($_GET['q'] ?? '');
if ($query === '') {
    echo json_encode(['results' => []]);
    exit;
}

$serviceUrl = getenv('SEARCH_API_URL') ?: '';
if ($serviceUrl !== '') {
    $joiner = str_contains($serviceUrl, '?') ? '&' : '?';
    $target = $serviceUrl . $joiner . http_build_query(['q' => $query]);
    $payload = fetch_json($target);
    if (isset($payload['results']) && is_array($payload['results'])) {
        echo json_encode(['results' => $payload['results']]);
        exit;
    }
}

$params = http_build_query([
    'action' => 'query',
    'format' => 'json',
    'list' => 'search',
    'srsearch' => $query,
    'utf8' => 1,
]);

$fallback = fetch_json('https://ko.wikipedia.org/w/api.php?' . $params);
if ($fallback === null) {
    http_response_code(502);
    echo json_encode(['error' => 'Search service is unavailable.']);
    exit;
}

$results = [];
if (isset($fallback['query']['search']) && is_array($fallback['query']['search'])) {
    foreach ($fallback['query']['search'] as $entry) {
        $title = $entry['title'] ?? '';
        $snippet = strip_tags($entry['snippet'] ?? '');
        $pageId = $entry['pageid'] ?? null;
        if ($title === '' || $pageId === null) {
            continue;
        }

        $results[] = [
            'title' => $title,
            'snippet' => $snippet,
            'url' => 'https://ko.wikipedia.org/?curid=' . $pageId,
        ];
    }
}

echo json_encode(['results' => $results]);

function fetch_json(string $url): ?array
{
    $response = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $context);
    }

    if ($response === false) {
        return null;
    }

    $decoded = json_decode($response, true);

    return is_array($decoded) ? $decoded : null;
}
