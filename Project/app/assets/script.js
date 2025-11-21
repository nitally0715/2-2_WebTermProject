// 핵심 Client Logic 전체를 담당
const SECTION_HIDDEN_CLASS = 'hidden';

// 페이지 로딩 시 초기화
document.addEventListener('DOMContentLoaded', () => {
    initMain();
    initTyping();
    initQuiz();
    initSearch();
});

function initMain() {
    if (!document.body.classList.contains('page-main')) {
        return;
    }

    const openBtn = document.getElementById('open-ranking');
    const closeBtn = document.getElementById('ranking-close');
    const overlay = document.getElementById('ranking-overlay');

    const setVisible = (show) => {
        if (!overlay) {
            return;
        }
        overlay.classList.toggle(SECTION_HIDDEN_CLASS, !show);
        overlay.setAttribute('aria-hidden', show ? 'false' : 'true');
    };

    openBtn?.addEventListener('click', () => setVisible(true));
    closeBtn?.addEventListener('click', () => setVisible(false));
    overlay?.addEventListener('click', (event) => {
        if (event.target === overlay) {
            setVisible(false);
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setVisible(false);
        }
    });
}

// 타자 연습 페이지 전용 로직
function initTyping() {
    if (!document.body.matches('[data-page="typing"]')) {
        return;
    }

    // 주요 DOM 요소 변수로 캐싱
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
    const accuracyEl = document.getElementById('typing-accuracy');
    const inputError = document.getElementById('typing-input-error');
    const summaryAvg = document.getElementById('typing-summary-average');
    const summaryBest = document.getElementById('typing-summary-best');
    const defaultInputErrorText = inputError ? inputError.textContent : '';
    const summaryAccuracy = document.getElementById('typing-summary-accuracy');

    // 게임 한 판에 20문장
    const TARGET_SENTENCES = 10;
    const CURRENT_SPEED_INTERVAL = 200;

    let rounds = [];
    let index = 0;
    let bestSpeed = 0;
    let sentenceStart = 0;
    let currentSentenceLength = 0;
    let currentPromptText = '';
    let speedSum = 0;
    let speedCount = 0;
    let weightedAccuracySum = 0;
    let currentSpeedTimer = null;

    // 섹션(선택/게임/결과) 표시 <= CSS의 .hidden 클래스 toggle
    const showSection = (name) => {
        Object.entries(sections).forEach(([key, section]) => {
            if (!section) {
                return;
            }
            section.classList.toggle(SECTION_HIDDEN_CLASS, key !== name);
        });
    };

    const clearInputState = () => {
        input.classList.remove('error', 'success');
        inputError?.classList.add(SECTION_HIDDEN_CLASS);
    };

    const applyInputFeedback = (isCorrect) => {
        input.classList.toggle('success', isCorrect);
        input.classList.toggle('error', !isCorrect);
        // 에러 메시지는 표시하지 않고 숨긴 상태를 유지
        inputError?.classList.add(SECTION_HIDDEN_CLASS);
    };

    const escapeHtml = (value) => value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const renderPromptHighlight = (typedValue) => {
        if (!promptEl) {
            return;
        }
        if (!typedValue) {
            promptEl.textContent = currentPromptText;
            return;
        }
        let html = '';
        const typedLength = typedValue.length;
        for (let i = 0; i < currentPromptText.length; i += 1) {
            const ch = currentPromptText[i];
            const isMismatch = i < typedLength && typedValue[i] !== ch;
            if (isMismatch) {
                html += `<span class="typing-mismatch">${escapeHtml(ch)}</span>`;
            } else {
                html += escapeHtml(ch);
            }
        }
        promptEl.innerHTML = html;
    };

    // 게임 데이터 초기화
    const resetGame = () => {
        stopRealtimeSpeed();
        rounds = [];
        index = 0;
        bestSpeed = 0;
        startedAt = 0;
        sentenceStart = 0;
        currentSentenceLength = 0;
        lastSentenceSpeed = 0;
        speedSum = 0;
        speedCount = 0;
        weightedAccuracySum = 0;
        input.value = '';
        clearInputState();
        updateStats();
        updateCurrentSpeed();
    };

    // 상태 갱신
    const updateStats = () => {
        const completed = index;
        const total = rounds.length || TARGET_SENTENCES;
        bestEl.textContent = bestSpeed.toString();
        progressEl.textContent = `${completed}/${total}`;
        counterEl.textContent = `${completed}/${total} 문장 완료`;
    };

    const countCorrectChars = (typed, target) => {
        const limit = Math.min(typed.length, target.length);
        let count = 0;
        for (let i = 0; i < limit; i += 1) {
            if (typed[i] === target[i]) {
                count += 1;
            }
        }
        return count;
    };

    const updateCurrentSpeed = () => {
        if (!currentEl || !input) {
            return;
        }
        const currentRound = rounds[index];
        if (!sentenceStart || !currentRound) {
            currentEl.textContent = '0';
            if (accuracyEl) {
                accuracyEl.textContent = '0 %';
            }
            return;
        }
        const elapsedMinutes = Math.max((Date.now() - sentenceStart) / 60000, 0.01);
        const typed = input.value;
        const typedLength = typed.length;
        const correctChars = countCorrectChars(typed, currentRound.sentence);
        const accuracy = typedLength ? Math.round((correctChars / typedLength) * 100) : 0;
        const currentSpeed = correctChars
            ? Math.round(correctChars / elapsedMinutes)
            : 0;
        currentEl.textContent = currentSpeed.toString();
        if (accuracyEl) {
            accuracyEl.textContent = `${accuracy} %`;
        }
        renderPromptHighlight(typed);
    };

    const startRealtimeSpeed = () => {
        updateCurrentSpeed();
        if (currentSpeedTimer) {
            return;
        }
        currentSpeedTimer = window.setInterval(updateCurrentSpeed, CURRENT_SPEED_INTERVAL);
    };

    const stopRealtimeSpeed = () => {
        if (currentSpeedTimer) {
            window.clearInterval(currentSpeedTimer);
            currentSpeedTimer = null;
        }
    };

    // 현재 문장 화면에 출력
    const showSentence = () => {
        const current = rounds[index];
        if (!current) {
            return;
        }
        languageLabel.textContent = current.language;
        promptEl.textContent = current.sentence;
        currentPromptText = current.sentence;
        counterEl.textContent = `${index}/${rounds.length}`;
        progressEl.textContent = `${index}/${rounds.length}`;
        input.value = '';
        input.focus();
        currentSentenceLength = current.sentence.length;
        sentenceStart = Date.now();
        updateCurrentSpeed();
        startRealtimeSpeed();
        if (accuracyEl) {
            accuracyEl.textContent = '0 %';
        }
    };

    const handleAdvance = (isCorrect = false) => {
        const current = rounds[index];
        if (!current) {
            return;
        }
        stopRealtimeSpeed();
        const typed = input.value;
        const typedLength = typed.length;
        index += 1;

        // 입력문장 타수(CPM) 계산, 최고속도(bestSpeed) 업데이트, 총합 speedSum 누적
        const elapsedMinutes = Math.max((Date.now() - sentenceStart) / 60000, 0.01);
        const correctChars = countCorrectChars(typed, current.sentence);
        const effectiveLength = Math.max(correctChars, 1);
        const sentenceSpeed = Math.max(
            1,
            Math.round(effectiveLength / elapsedMinutes)
        );
        lastSentenceSpeed = sentenceSpeed;
        if (isCorrect) {
            bestSpeed = Math.max(bestSpeed, sentenceSpeed);
        }
        speedSum += sentenceSpeed;
        speedCount += 1;
        const sentenceAccuracy = typedLength ? correctChars / typedLength : 0;
        const lengthRatio = currentSentenceLength
            ? Math.min(typedLength / currentSentenceLength, 1)
            : 0;
        const weightedAccuracy = sentenceAccuracy * lengthRatio;
        weightedAccuracySum += weightedAccuracy;

        // api/update_score.php 로 점수 전송
        updateStats();
        submitProgress('/api/update_score.php', {
            type: 'typing',
            typedSentences: 1,
            bestSpeed,
        });

        // 다음 문장 or 종료
        if (index >= rounds.length) {
            finishGame();
        } else {
            showSentence();
        }
    };

    // 게임 종료
    const finishGame = () => {
        stopRealtimeSpeed();
        const averageSpeed = speedCount ? Math.round(speedSum / speedCount) : 0;
        const averageAccuracy = speedCount
            ? Math.round((weightedAccuracySum / speedCount) * 100)
            : 0;
        summaryAvg.textContent = averageSpeed.toString();
        summaryBest.textContent = bestSpeed.toString();
        if (summaryAccuracy) {
            summaryAccuracy.textContent = `${averageAccuracy} %`;
        }
        showSection('summary');
    };

    // 복붙 시도하면 에러발생
    const showClipboardBlocked = () => {
        if (!input || !inputError) {
            return;
        }
        input.classList.add('error');
        inputError.textContent = '어허! 붙여넣을 수 없습니다.';
        inputError.classList.remove(SECTION_HIDDEN_CLASS);
        window.setTimeout(() => {
            input.classList.remove('error');
            inputError.textContent = defaultInputErrorText;
            inputError.classList.add(SECTION_HIDDEN_CLASS);
        }, 1500);
    };

    const blockClipboardEvents = (event) => {
        event.preventDefault();
        showClipboardBlocked();
    };

    // 선택된 언어 목록으로 문장 pool 생성 (랜덤 선택)
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
        weightedAccuracySum = 0;
        languageError.classList.add(SECTION_HIDDEN_CLASS);
        showSection('game');
        showSentence();
        updateStats();
        startRealtimeSpeed();
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

    input?.addEventListener('paste', blockClipboardEvents);
    input?.addEventListener('drop', blockClipboardEvents);

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const current = rounds[index];
            if (!current) {
                return;
            }
            const isCorrect = input.value.trim() === current.sentence.trim();
            handleAdvance(isCorrect);
            requestAnimationFrame(() => {
                applyInputFeedback(isCorrect);
                window.setTimeout(clearInputState, 400);
            });
        }
    });

    input?.addEventListener('input', () => {
        clearInputState();
        updateCurrentSpeed();
        renderPromptHighlight(input.value);
    });

    showSection('select');
}

// 퀴즈 페이지 전용 로직
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
    const submitButton = document.getElementById('quiz-submit');
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

    const TARGET_QUESTIONS = 5;
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
                pendingTimeout = null;
                proceed();
            }, 2000);
        } else {
            wrong += 1;
            showFeedback(`오답입니다! 정답: ${current.keyword}`, true);
            pendingTimeout = window.setTimeout(() => {
                input.removeAttribute('disabled');
                feedbackEl?.classList.add(SECTION_HIDDEN_CLASS);
                pendingTimeout = null;
                proceed();
            }, 2000);
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

        const shuffled = shuffle(pool);
        if (shuffled.length >= TARGET_QUESTIONS) {
            rounds = shuffled.slice(0, TARGET_QUESTIONS);
        } else {
            const needed = TARGET_QUESTIONS - shuffled.length;
            const extras = [];
            for (let i = 0; i < needed; i += 1) {
                extras.push(shuffled[i % shuffled.length]);
            }
            rounds = [...shuffled, ...extras];
        }
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

    submitButton?.addEventListener('click', () => {
        if (pendingTimeout) {
            return;
        }
        evaluateAnswer();
    });

    showSection('select');
}

// 검색 페이지 전용 로직
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

// 공용함수: 순서 랜덤 섞기
function shuffle(array) {
    const clone = [...array];
    for (let i = clone.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [clone[i], clone[j]] = [clone[j], clone[i]];
    }
    return clone;
}

// 점수 서버에 전송(fetch API 호출)
function submitProgress(url, payload) {
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin',
    }).catch(() => {});
}
