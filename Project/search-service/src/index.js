// Express.js 기반[Node.js의 대표적인 웹 프레임워크]의 간단한 REST API
// 사용자가 보낸 검색어로 위키백과에 요청 -> 결과 가공후 json 형태로 변환
import express from 'express';
import fetch from 'node-fetch'; 

const app = express();
const PORT = process.env.PORT || 3000;
// 위키백과 API endpoint 한국어 ver
const WIKI_ENDPOINT = 'https://ko.wikipedia.org/w/api.php';

// 라우트 정의
app.get('/search', async (req, res) => {
    const query = (req.query.q ?? '').toString().trim();

    if (!query) {
        res.json({ results: [] });
        return;
    }

    // 위키백과 API에 필요한 파라미터
    const params = new URLSearchParams({
        action: 'query',
        format: 'json',
        list: 'search',
        srsearch: query,
        utf8: '1',
    });

    // 무한 대기 Deadlock, 서버 응답 지연 방지
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 5000);

    // API 호출
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

        // 결과 파싱 및 가공
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

// Express 서버 시작
app.listen(PORT, () => {});
