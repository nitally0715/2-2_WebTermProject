CREATE DATABASE IF NOT EXISTS jctyping CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jctyping;

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

CREATE TABLE IF NOT EXISTS typing_sentences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language VARCHAR(50) NOT NULL,
    sentence TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language VARCHAR(50) NOT NULL,
    keyword VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
('Python', 'generator', 'Function that yields lazy sequences using the yield keyword.');
