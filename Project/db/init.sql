/* jctyping DB 생성, 명령들 jctyping DB 안에서 실행 */
CREATE DATABASE IF NOT EXISTS jctyping CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jctyping;

/* users table[회원 정보] 
    1. id (자동 증가하는 기본 키): AUTO_INCREMENT
    2. 사용자 이름(unique)
    3. 비밀번호(해시값)
    4. 타자 연습 총 횟수
    5. 퀴즈 총 풀이 수
    6. 사용자 level(누적 성과 기반)
    7. 최고 타자 속도
    8. 계정 생성 시각
*/
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    total_typing INT NOT NULL DEFAULT 0,
    total_quiz INT NOT NULL DEFAULT 0,
    level INT NOT NULL DEFAULT 0,
    highest_speed INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* typing_sentences table[타자 연습 문장]: 언어별로 저장된 테이블
    1. id(기본 키, int)
    2. language: 프로그래밍 언어
    3. sentence: text 연습 문장 내용 
*/
CREATE TABLE IF NOT EXISTS typing_sentences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language VARCHAR(50) NOT NULL,
    sentence TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* quiz_questions table[퀴즈 문제] typing_sentence와 비슷한 구조와 목적
    1. id
    2. language: 프로그래밍 언어
    3. keyword: 핵심 키워드(정답)
    4. description: 해당 키워드의 설명 또는 문제 내용
*/
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language VARCHAR(50) NOT NULL,
    keyword VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* typing_sentence table에 저장: 공식 레퍼런스 참조하여 하드코딩으로 업데이트 예정 */
INSERT INTO typing_sentences (language, sentence) VALUES
('C++', 'std::vector<int> scores = {1, 2, 3};'),
('C++', 'for(auto &item : container) { process(item); }'),
('C++', 'class Player { public: std::string name; };'),
('C++', 'constexpr int kBufferSize = 256;'),
('C++', 'std::unique_ptr<Logger> logger = std::make_unique<Logger>();'),
('C++', 'template<typename T> void Swap(T& a, T& b) { T tmp = a; a = b; b = tmp; }'),
('C++', 'enum class Difficulty { Easy, Normal, Hard };'),
('C++', 'std::optional<int> result = MaybeCompute();'),
('C++', 'std::sort(values.begin(), values.end());'),
('C++', 'if(stream.fail()) { throw std::runtime_error("failed"); }'),
('Java', 'List<String> users = new ArrayList<>();'),
('Java', '@Override public String toString() { return name; }'),
('Java', 'try (var reader = Files.newBufferedReader(path)) { ... }'),
('Java', 'record Score(String user, int value) {}'),
('Java', 'CompletableFuture.runAsync(() -> sync());'),
('Java', 'Stream.of(items).filter(Objects::nonNull).toList();'),
('Java', 'if(response.isEmpty()) { return Optional.empty(); }'),
('Java', 'Map<String, Integer> cache = new HashMap<>();'),
('Java', 'var now = Instant.now();'),
('Java', 'public sealed interface Command permits Start, Stop {}'),
('Python', 'numbers = [n for n in range(10) if n % 2 == 0]'),
('Python', 'config: dict[str, str] = {"theme": "dark"}'),
('Python', 'with open("log.txt", "a", encoding="utf-8") as handler:'),
('Python', 'def greet(name: str) -> str: return f"Hello {name}"'),
('Python', 'from dataclasses import dataclass'),
('Python', '@dataclass class Point: x: int; y: int'),
('Python', 'match event: case {"type": "start"}: handle()'),
('Python', 'total = sum(values) if values else 0'),
('Python', 'paths = sorted(Path(".").glob("*.py"))'),
('Python', 'if __name__ == "__main__": main()');

/* quiz_questions table에 저장: 상동 */
INSERT INTO quiz_questions (language, keyword, description) VALUES
('C++', 'std::move', 'Casts an lvalue reference into an rvalue reference to trigger move semantics.'),
('C++', 'emplace_back', 'Constructs an element in-place at the end of a container.'),
('C++', 'constexpr', 'Marks a value or function that can be evaluated at compile time.'),
('C++', 'shared_ptr', 'Reference-counted smart pointer that shares ownership.'),
('Java', 'Optional', 'Container object used to express the presence or absence of a value.'),
('Java', 'synchronized', 'Keyword that secures a critical section with a monitor lock.'),
('Java', 'Stream', 'Abstraction that supports functional-style operations on collections.'),
('Java', 'record', 'Concise syntax for immutable data carriers with generated members.'),
('Python', 'list comprehension', 'Expression that builds a list from an iterable in a single line.'),
('Python', 'context manager', 'Protocol that controls setup and teardown around a with block.'),
('Python', 'dataclass', 'Decorator adding generated methods to simple data containers.'),
('Python', 'generator', 'Function that yields lazy sequences using the yield keyword.'),
('C++', 'lambda', 'Inline function object created with [] capture syntax.'),
('Java', 'lambda', 'Concise anonymous function introduced in Java 8.'),
('Python', 'decorator', 'Function that wraps another function to modify behavior.'),
('C++', 'std::forward', 'Perfect-forwarding utility to preserve value category for function arguments.'),
('C++', 'unique_ptr', 'Move-only smart pointer owning a single object with deterministic release.'),
('C++', 'RAII', 'Resource Acquisition Is Initialization: manage resources via object lifetime.'),
('Java', 'try-with-resources', 'Automatic resource management using try(...) to close resources.'),
('Java', 'CompletableFuture', 'Asynchronous computation stage supporting chaining and composition.'),
('Java', 'Garbage Collector', 'JVM component that automatically reclaims unused objects.'),
('Python', 'virtualenv', 'Isolated Python environment with its own interpreter and packages.'),
('Python', 'type hint', 'Static-friendly annotations that declare expected types of values.'),
('Python', 'list slicing', 'Syntax to take sublists via start:stop:step on sequences.');
