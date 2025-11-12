import express from 'express';
import fetch from 'node-fetch';

const app = express();
const PORT = process.env.PORT || 3000;
const WIKI_ENDPOINT = 'https://ko.wikipedia.org/w/api.php';

app.get('/search', async (req, res) => {
    const query = (req.query.q ?? '').toString().trim();

    if (!query) {
        res.json({ results: [] });
        return;
    }

    const params = new URLSearchParams({
        action: 'query',
        format: 'json',
        list: 'search',
        srsearch: query,
        utf8: '1',
    });

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 5000);

    try {
        const response = await fetch(`${WIKI_ENDPOINT}?${params.toString()}`, {
            headers: {
                'User-Agent': 'JCTypingSearchService/1.0 (+https://example.com)',
            },
            signal: controller.signal,
        });

        if (!response.ok) {
            throw new Error(`Remote API error: ${response.status}`);
        }

        const data = await response.json();
        const items = data?.query?.search ?? [];
        const results = items.map((entry) => ({
            title: entry.title,
            snippet: entry.snippet?.replace(/<\/?[^>]+(>|$)/g, '') ?? '',
            url: `https://ko.wikipedia.org/?curid=${entry.pageid}`,
        }));
        res.json({ results });
    } catch (error) {
        res.status(502).json({ error: 'Upstream search failed.' });
    } finally {
        clearTimeout(timeout);
    }
});

app.listen(PORT, () => {
    // Deliberately avoiding user data logging for compliance with project rules.
});
