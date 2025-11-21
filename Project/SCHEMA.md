# 정컴타자연습

README.md에서 정의한 요구사항을 바탕으로 구성한 웹 애플리케이션입니다. HTML/CSS/JS 기반의 프론트엔드와 PHP 백엔드, MySQL 데이터베이스를 사용하며 Docker 환경에서 손쉽게 실행할 수 있습니다.

## 주요 기능
- **로그인 / 회원가입**: 세션 기반 인증, 비밀번호 해시 저장.
- **메인 대시보드**: 가입일, 레벨, 누적 타수/퀴즈 수, 최고 타수 표시 및 게임 메뉴 제공.
- **타자연습**: 언어별 문장을 선택해 20개 문장을 따라 치며 현재/최고 타수를 실시간 표시하고 결과를 DB에 반영.
- **퀴즈**: 언어 선택 후 함수 설명을 보고 키워드를 입력, 정·오답 카운트와 진행도를 제공.
- **검색**: Node.js로 작성된 별도 검색 서비스가 Wikipedia API를 대리 호출하여 결과를 반환.

## 프로젝트 구조
```
app/                # PHP 애플리케이션 루트
  api/              # Ajax 엔드포인트 (점수 갱신, 검색)
  assets/           # 공용 CSS/JS
  data/             # 참고용 JSON 데이터 셋
  includes/         # DB, 인증, 헬퍼 함수
  *.php             # 개별 화면 (로그인, 메인, 게임 등)
db/
  init.sql          # MySQL 스키마 및 기본 데이터
search-service/     # Node.js 기반 검색 API
docker-compose.yml  # 웹 + DB 서비스 정의
Dockerfile          # Apache + PHP 컨테이너 정의
AGENT.md            # 기획 요구사항
```

## 실행 방법
1. **필수 도구**  
   - Docker 20+  
   - Docker Compose v2

2. **컨테이너 기동**
   ```bash
   docker-compose up --build
   ```
   - 웹: http://localhost:8080
   - DB: MySQL 8.0 (`jctyping` / `secret`)
   - 검색 서비스(Node): http://localhost:8090/search?q=검색어

3. **초기 상태**
   - 회원 정보는 비어 있으므로 `Register` 페이지에서 계정을 만든 뒤 로그인합니다.
   - `db/init.sql`이 실행되며 기본 타자 문장과 퀴즈가 자동으로 채워집니다.

4. **종료**
   ```bash
   docker-compose down
   ```

## 개발 메모
- 사용자 정보는 세션에 최소한으로만 저장하며, 민감한 로그를 남기지 않습니다.
- `api/update_score.php`는 타자/퀴즈 결과를 받아 누적 타수·퀴즈 수, 최고 타수, 레벨을 갱신합니다.
- `app/assets/script.js`는 각 화면(타자, 퀴즈, 검색)을 동적으로 제어하며, 선택 누락 시 시각적 경고(빨간 테두리)를 제공합니다.
- PHP는 `SEARCH_API_URL` 환경 변수를 통해 Node 검색 서비스를 호출하며, 해당 변수가 없으면 직접 Wikipedia API를 사용하도록 폴백합니다.
- Docker 환경 외 로컬 PHP 서버를 쓸 경우 `.env` 혹은 서버 설정으로 `MYSQL_*` 환경 변수를 제공합니다.

## 테스트 팁
- `typing_sentences`, `quiz_questions` 테이블에 항목을 추가하면 자동으로 게임 화면에 반영됩니다.
- 위키 검색은 외부 네트워크가 필요한 기능이므로 방화벽/프록시 환경에서는 별도 허용이 필요합니다.
