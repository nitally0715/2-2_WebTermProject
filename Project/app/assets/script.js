const SECTION_HIDDEN_CLASS = 'hidden';

document.addEventListener('DOMContentLoaded', () => {
    initTyping();
    initQuiz();
    initSearch();
});

function initTyping() {
    if (!document.body.matches('[data-page="typing"]')) {
        return;
    }

    const data = window.typingData || {};
    const sentences = data.sentences || {};
    const sections = {
        select: document.querySelector('#typing-app [data-section="select"]'),
        game: document.querySelector('#typing-app [data-section="game"]'),
        summary: document.querySelector('#typing-app [data-section="summary"]'),
    };
    const startButton = document.getElementById('typing-start');
    const restartButton = document.getElementById('typing-restart');
    const exitButton = document.getElementById('typing-exit');
    const languageError = document.getElementById('typing-language-error');
    const input = document.getElementById('typing-input');
    const promptEl = document.getElementById('typing-prompt');
    const languageLabel = document.getElementById('typing-language-label');
    const counterEl = document.getElementById('typing-counter');
    const currentEl = document.getElementById('typing-current');
    const bestEl = document.getElementById('typing-best');
    const progressEl = document.getElementById('typing-progress');
    const inputError = document.getElementById('typing-input-error');
    const summaryAvg = document.getElementById('typing-summary-average');
    const summaryBest = document.getElementById('typing-summary-best');

    const TARGET_SENTENCES = 20;

    let rounds = [];
    let index = 0;
    let bestSpeed = 0;
    let startedAt = 0;
    let sentenceStart = 0;
    let currentSentenceLength = 0;
    let lastSentenceSpeed = 0;
    let speedSum = 0;
    let speedCount = 0;

    const showSection = (name) => {
        Object.entries(sections).forEach(([key, section]) => {
            if (!section) {
                return;
            }
            section.classList.toggle(SECTION_HIDDEN_CLASS, key !== name);
        });
    };

    const resetGame = () => {
        rounds = [];
        index = 0;
        bestSpeed = 0;
        startedAt = 0;
        sentenceStart = 0;
        currentSentenceLength = 0;
        lastSentenceSpeed = 0;
        speedSum = 0;
        speedCount = 0;
        input.value = '';
        input.classList.remove('error');
        inputError?.classList.add(SECTION_HIDDEN_CLASS);
        updateStats();
    };

    const updateStats = () => {
        const completed = index;
        const total = rounds.length || TARGET_SENTENCES;
        currentEl.textContent = lastSentenceSpeed.toString();
        bestEl.textContent = bestSpeed.toString();
        progressEl.textContent = `${completed}/${total}`;
        counterEl.textContent = `${completed}/${total} 문장 완료`;
    };

    const showSentence = () => {
        const current = rounds[index];
        if (!current) {
            return;
        }
        languageLabel.textContent = current.language;
        promptEl.textContent = current.sentence;
        counterEl.textContent = `${index}/${rounds.length}`;
        progressEl.textContent = `${index}/${rounds.length}`;
        input.value = '';
        input.focus();
        currentSentenceLength = current.sentence.length;
        sentenceStart = Date.now();
    };

    const handleSuccess = () => {
        const current = rounds[index];
        index += 1;

        const elapsedMinutes = Math.max((Date.now() - sentenceStart) / 60000, 0.01);
        const sentenceSpeed = Math.max(
            1,
            Math.round((currentSentenceLength / 5) / elapsedMinutes)
        );
        lastSentenceSpeed = sentenceSpeed;
        bestSpeed = Math.max(bestSpeed, sentenceSpeed);
        speedSum += sentenceSpeed;
        speedCount += 1;

        updateStats();
        submitProgress('/api/update_score.php', {
            type: 'typing',
            typedSentences: 1,
            bestSpeed,
        });

        if (index >= rounds.length) {
            finishGame();
        } else {
            showSentence();
        }
    };

    const finishGame = () => {
        const averageSpeed = speedCount ? Math.round(speedSum / speedCount) : 0;
        summaryAvg.textContent = averageSpeed.toString();
        summaryBest.textContent = bestSpeed.toString();
        showSection('summary');
    };

    const startGame = (selectedLanguages) => {
        const pool = [];
        selectedLanguages.forEach((lang) => {
            (sentences[lang] || []).forEach((sentence) => {
                pool.push({ language: lang, sentence });
            });
        });

        if (!pool.length) {
            languageError.textContent = '선택한 언어의 문장이 없습니다.';
            languageError.classList.remove(SECTION_HIDDEN_CLASS);
            return;
        }

        rounds = shuffle(pool).slice(0, Math.min(TARGET_SENTENCES, pool.length));
        index = 0;
        bestSpeed = 0;
        startedAt = Date.now();
        sentenceStart = 0;
        currentSentenceLength = 0;
        lastSentenceSpeed = 0;
        speedSum = 0;
        speedCount = 0;
        languageError.classList.add(SECTION_HIDDEN_CLASS);
        showSection('game');
        showSentence();
        updateStats();
    };

    startButton?.addEventListener('click', () => {
        const checked = Array.from(
            document.querySelectorAll('input[name="typing-language"]:checked')
        ).map((input) => input.value);

        if (!checked.length) {
            languageError.textContent = '언어를 하나 이상 선택해야 합니다.';
            languageError.classList.remove(SECTION_HIDDEN_CLASS);
            return;
        }
        resetGame();
        startGame(checked);
    });

    restartButton?.addEventListener('click', () => {
        showSection('select');
    });

    exitButton?.addEventListener('click', () => {
        resetGame();
        showSection('select');
    });

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const current = rounds[index];
            if (!current) {
                return;
            }
            if (input.value.trim() === current.sentence.trim()) {
                input.classList.remove('error');
                inputError.classList.add(SECTION_HIDDEN_CLASS);
                handleSuccess();
            } else {
                input.classList.add('error');
                inputError.classList.remove(SECTION_HIDDEN_CLASS);
            }
        }
    });

    showSection('select');
}

function initQuiz() {
    if (!document.body.matches('[data-page="quiz"]')) {
        return;
    }

    const data = window.quizData || {};
    const questionsByLanguage = data.questions || {};
    const sections = {
        select: document.querySelector('#quiz-app [data-section="select"]'),
        game: document.querySelector('#quiz-app [data-section="game"]'),
        summary: document.querySelector('#quiz-app [data-section="summary"]'),
    };

    const languageError = document.getElementById('quiz-language-error');
    const startButton = document.getElementById('quiz-start');
    const restartButton = document.getElementById('quiz-restart');
    const exitButton = document.getElementById('quiz-exit');
    const input = document.getElementById('quiz-input');
    const promptEl = document.getElementById('quiz-prompt');
    const languageLabel = document.getElementById('quiz-language-label');
    const counterEl = document.getElementById('quiz-counter');
    const progressEl = document.getElementById('quiz-progress');
    const correctEl = document.getElementById('quiz-correct');
    const wrongEl = document.getElementById('quiz-wrong');
    const feedbackEl = document.getElementById('quiz-feedback');
    const summaryCorrect = document.getElementById('quiz-summary-correct');
    const summaryWrong = document.getElementById('quiz-summary-wrong');

    const TARGET_QUESTIONS = 20;
    let rounds = [];
    let index = 0;
    let correct = 0;
    let wrong = 0;
    let pendingTimeout = null;

    const showSection = (name) => {
        Object.entries(sections).forEach(([key, section]) => {
            if (!section) {
                return;
            }
            section.classList.toggle(SECTION_HIDDEN_CLASS, key !== name);
        });
    };

    const resetGame = () => {
        rounds = [];
        index = 0;
        correct = 0;
        wrong = 0;
        feedbackEl?.classList.add(SECTION_HIDDEN_CLASS);
        feedbackEl.textContent = '';
        updateQuizStats();
    };

    const updateQuizStats = () => {
        correctEl.textContent = correct.toString();
        wrongEl.textContent = wrong.toString();
        progressEl.textContent = `${index}/${rounds.length || TARGET_QUESTIONS}`;
        counterEl.textContent = `${index}/${rounds.length || TARGET_QUESTIONS}`;
    };

    const showQuestion = () => {
        const current = rounds[index];
        if (!current) {
            return;
        }
        promptEl.textContent = current.description;
        languageLabel.textContent = current.language;
        counterEl.textContent = `${index}/${rounds.length}`;
        progressEl.textContent = `${index}/${rounds.length}`;
        input.value = '';
        input.focus();
    };

    const finishGame = () => {
        summaryCorrect.textContent = correct.toString();
        summaryWrong.textContent = wrong.toString();
        showSection('summary');
    };

    const proceed = () => {
        index += 1;
        updateQuizStats();
        if (index >= rounds.length) {
            finishGame();
        } else {
            showQuestion();
        }
    };

    const showFeedback = (message, isError) => {
        if (!feedbackEl) {
            return;
        }
        feedbackEl.textContent = message;
        feedbackEl.classList.toggle('error', Boolean(isError));
        feedbackEl.classList.remove(SECTION_HIDDEN_CLASS);
    };

    const evaluateAnswer = () => {
        const current = rounds[index];
        if (!current) {
            return;
        }
        const value = input.value.trim();
        if (!value) {
            return;
        }

        input.setAttribute('disabled', 'disabled');

        if (value.toLowerCase() === current.keyword.toLowerCase()) {
            correct += 1;
            showFeedback('정답입니다!', false);
            submitProgress('/api/update_score.php', {
                type: 'quiz',
                correctAnswers: 1,
            });
            pendingTimeout = window.setTimeout(() => {
                input.removeAttribute('disabled');
                feedbackEl?.classList.add(SECTION_HIDDEN_CLASS);
                proceed();
            }, 500);
        } else {
            wrong += 1;
            showFeedback(`오답! 정답: ${current.keyword}`, true);
            pendingTimeout = window.setTimeout(() => {
                input.removeAttribute('disabled');
                feedbackEl?.classList.add(SECTION_HIDDEN_CLASS);
                proceed();
            }, 800);
        }
    };

    const startGame = (selectedLanguages) => {
        const pool = [];
        selectedLanguages.forEach((lang) => {
            (questionsByLanguage[lang] || []).forEach((item) => {
                pool.push({
                    language: lang,
                    keyword: item.keyword,
                    description: item.description,
                });
            });
        });

        if (!pool.length) {
            languageError.textContent = '선택한 언어의 문제가 없습니다.';
            languageError.classList.remove(SECTION_HIDDEN_CLASS);
            return;
        }

        rounds = shuffle(pool).slice(0, Math.min(TARGET_QUESTIONS, pool.length));
        index = 0;
        correct = 0;
        wrong = 0;
        languageError.classList.add(SECTION_HIDDEN_CLASS);
        showSection('game');
        updateQuizStats();
        showQuestion();
    };

    startButton?.addEventListener('click', () => {
        const checked = Array.from(
            document.querySelectorAll('input[name="quiz-language"]:checked')
        ).map((input) => input.value);

        if (!checked.length) {
            languageError.textContent = '언어를 하나 이상 선택해야 합니다.';
            languageError.classList.remove(SECTION_HIDDEN_CLASS);
            return;
        }
        resetGame();
        startGame(checked);
    });

    restartButton?.addEventListener('click', () => {
        resetGame();
        showSection('select');
        languageError.classList.add(SECTION_HIDDEN_CLASS);
    });

    exitButton?.addEventListener('click', () => {
        if (pendingTimeout) {
            clearTimeout(pendingTimeout);
        }
        resetGame();
        showSection('select');
        languageError.classList.add(SECTION_HIDDEN_CLASS);
    });

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (pendingTimeout) {
                return;
            }
            evaluateAnswer();
        }
    });

    showSection('select');
}

function initSearch() {
    if (!document.body.matches('[data-page="search"]')) {
        return;
    }

    const input = document.getElementById('search-input');
    const button = document.getElementById('search-button');
    const resultsEl = document.getElementById('search-results');
    const errorEl = document.getElementById('search-error');

    const renderResults = (items) => {
        if (!resultsEl) {
            return;
        }
        if (!items.length) {
            resultsEl.innerHTML = '<p class="muted">검색 결과가 없습니다.</p>';
            return;
        }
        resultsEl.innerHTML = '';
        items.forEach((item) => {
            const article = document.createElement('article');
            const title = document.createElement('h3');
            const snippet = document.createElement('p');
            title.textContent = item.title;
            snippet.textContent = item.snippet;
            article.appendChild(title);
            article.appendChild(snippet);
            article.addEventListener('click', () => {
                window.open(item.url, '_blank');
            });
            resultsEl.appendChild(article);
        });
    };

    const performSearch = () => {
        const query = input.value.trim();
        if (!query) {
            errorEl.classList.remove(SECTION_HIDDEN_CLASS);
            return;
        }
        errorEl.classList.add(SECTION_HIDDEN_CLASS);

        fetch(`/api/search_api.php?q=${encodeURIComponent(query)}`)
            .then((response) => response.json())
            .then((payload) => {
                if (payload.error) {
                    throw new Error(payload.error);
                }
                renderResults(payload.results || []);
            })
            .catch(() => {
                renderResults([]);
                errorEl.textContent = '검색 중 오류가 발생했습니다.';
                errorEl.classList.remove(SECTION_HIDDEN_CLASS);
            });
    };

    button?.addEventListener('click', performSearch);
    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            performSearch();
        }
    });
}

function shuffle(array) {
    const clone = [...array];
    for (let i = clone.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [clone[i], clone[j]] = [clone[j], clone[i]];
    }
    return clone;
}

function submitProgress(url, payload) {
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin',
    }).catch(() => {
        // intentionally swallow errors; progress can be retried later
    });
}
