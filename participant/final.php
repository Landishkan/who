<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getDB();
    
    // Получаем все активные конечные тесты
    $tests = $pdo->query("
        SELECT 
            t.id as test_id,
            s.district,
            s.institution,
            s.age_group,
            s.format,
            s.created_at
        FROM tests t
        JOIN sessions s ON t.session_id = s.id
        WHERE t.type = 'final' 
        AND t.is_active = 1 
        AND s.is_hidden = 0
        ORDER BY s.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Ошибка загрузки тестов");
}

function getDeviceHash() {
    $fingerprint = $_SERVER['REMOTE_ADDR'] . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
    return hash('sha256', $fingerprint);
}

$test_id = $_GET['test_id'] ?? null;
$device_hash = getDeviceHash();
$already_passed = false;
$questions = [];

if ($test_id) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM answers WHERE test_id = ? AND device_hash = ?");
        $stmt->execute([$test_id, $device_hash]);
        if ($stmt->fetch()) {
            $already_passed = true;
        } else {
            $stmt = $pdo->prepare("
                SELECT id, text, theme 
                FROM questions 
                WHERE test_type = 'final' 
                ORDER BY theme, id
            ");
            $stmt->execute();
            $all_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $questions = [];
            foreach ($all_questions as $q) {
                if (!isset($questions[$q['theme']])) {
                    $questions[$q['theme']] = [];
                }
                $questions[$q['theme']][] = $q;
            }
        }
    } catch (Exception $e) {
        // Игнорируем
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конечный тест | КиберКвестор</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Те же стили что и в initial.php */
        .test-container { max-width: 900px; margin: 30px auto; }
        .test-list { display: grid; gap: 20px; }
        .test-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 25px; cursor: pointer; transition: all 0.3s ease; }
        .test-card:hover { border-color: rgba(75, 205, 235, 0.8); box-shadow: 0 0 30px rgba(75, 205, 235, 0.3); transform: translateY(-3px); }
        .test-card h3 { font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 10px; font-size: 20px; }
        .test-card .meta { color: rgba(255, 255, 255, 0.7); font-size: 14px; display: flex; gap: 15px; flex-wrap: wrap; }
        .test-form { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 30px; }
        .theme-section { margin-bottom: 30px; }
        .theme-title { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid rgba(75, 205, 235, 0.3); text-transform: uppercase; }
        .question-block { margin-bottom: 25px; padding: 20px; background: rgba(0, 0, 0, 0.3); border-radius: 8px; border-left: 4px solid #4bcde7; }
        .question-text { font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 600; color: #fff; margin-bottom: 15px; }
        .answer-buttons { display: flex; gap: 15px; flex-wrap: wrap; }
        .answer-btn { flex: 1; min-width: 150px; padding: 15px 25px; background: rgba(75, 205, 235, 0.1); border: 2px solid rgba(75, 205, 235, 0.5); border-radius: 8px; color: #fff; font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .answer-btn:hover { background: rgba(75, 205, 235, 0.3); border-color: #4bcde7; }
        .answer-btn.selected { background: #4bcde7; color: #000; border-color: #4bcde7; }
        .submit-btn { display: block; width: 100%; padding: 18px; background: linear-gradient(135deg, #4bcde7 0%, #254883 100%); border: none; border-radius: 8px; color: #fff; font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: all 0.3s ease; margin-top: 30px; }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(75, 205, 235, 0.5); }
        .already-passed { text-align: center; padding: 60px 20px; }
        .already-passed i { font-size: 64px; color: #4bcde7; margin-bottom: 20px; }
        .already-passed h2 { font-family: 'Orbitron', monospace; color: #4bcde7; margin-bottom: 15px; }
        .empty-state { text-align: center; padding: 60px 20px; color: rgba(255, 255, 255, 0.6); }
        .empty-state i { font-size: 48px; margin-bottom: 15px; color: rgba(75, 205, 235, 0.3); }
        .back-link { display: inline-block; margin-top: 20px; padding: 12px 30px; background: rgba(255, 255, 255, 0.1); border: 2px solid #4bcde7; border-radius: 8px; color: #4bcde7; text-decoration: none; font-family: 'Rajdhani', sans-serif; font-weight: 600; transition: all 0.3s ease; }
        .back-link:hover { background: rgba(75, 205, 235, 0.2); }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Конечный тест</p>
            </div>
            <nav class="header-nav">
                <a href="../index.html" class="nav-button">
                    <i class="fas fa-home"></i> На главную
                </a>
            </nav>
        </header>
        
        <main class="main-content test-container animate__animated animate__fadeIn">
            <?php if ($already_passed): ?>
                <div class="test-form">
                    <div class="already-passed">
                        <i class="fas fa-check-circle"></i>
                        <h2>Вы уже прошли этот тест!</h2>
                        <p style="color: rgba(255,255,255,0.7); font-family: 'Roboto', sans-serif;">
                            Спасибо за участие!
                        </p>
                        <a href="final.php" class="back-link">
                            <i class="fas fa-arrow-left"></i> Вернуться к списку
                        </a>
                    </div>
                </div>
                
            <?php elseif (!empty($questions)): ?>
                <div class="test-form">
                    <form id="testForm" method="POST" action="submit-answer.php">
                        <input type="hidden" name="test_id" value="<?= e($test_id) ?>">
                        
                        <?php 
                        $themeNames = [
                            'bullying' => '️ Буллинг',
                            'fraud' => '💰 Мошенничество',
                            'etiquette' => '💬 Сетевой этикет',
                            'fakes' => '📰 Фейки'
                        ];
                        
                        foreach ($questions as $theme => $themeQuestions): 
                        ?>
                            <div class="theme-section">
                                <div class="theme-title"><?= $themeNames[$theme] ?? $theme ?></div>
                                
                                <?php foreach ($themeQuestions as $question): ?>
                                    <div class="question-block">
                                        <div class="question-text"><?= e($question['text']) ?></div>
                                        <div class="answer-buttons">
                                            <button type="button" class="answer-btn" 
                                                    data-question="<?= $question['id'] ?>" 
                                                    data-answer="agree">
                                                <i class="fas fa-check"></i> Согласен
                                            </button>
                                            <button type="button" class="answer-btn" 
                                                    data-question="<?= $question['id'] ?>" 
                                                    data-answer="disagree">
                                                <i class="fas fa-times"></i> Не согласен
                                            </button>
                                        </div>
                                        <input type="hidden" name="answers[<?= $question['id'] ?>]" class="answer-input" required>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Отправить ответы
                        </button>
                    </form>
                </div>
                
            <?php else: ?>
                <?php if (empty($tests)): ?>
                    <div class="test-card" style="text-align: center;">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Сейчас нет активных тестов</h3>
                            <p>Пожалуйста, дождитесь, пока спикер создаст тест</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="test-list">
                        <?php foreach ($tests as $test): ?>
                            <div class="test-card" onclick="location.href='?test_id=<?= $test['test_id'] ?>'">
                                <h3><?= e($test['institution']) ?></h3>
                                <div class="meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?= e($test['district']) ?> район</span>
                                    <span><i class="fas fa-calendar"></i> <?= date('d.m.Y', strtotime($test['created_at'])) ?></span>
                                    <?php if ($test['age_group']): ?>
                                        <span><i class="fas fa-users"></i> <?= e($test['age_group']) ?></span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-graduation-cap"></i> <?= e($test['format']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Конечный тест</div>
            <div class="footer-links">
                <a href="../index.html" class="footer-link">Главная</a>
            </div>
        </footer>
    </div>
    
    <script>
        document.querySelectorAll('.answer-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const questionId = this.dataset.question;
                const answer = this.dataset.answer;
                
                const parent = this.closest('.question-block');
                parent.querySelectorAll('.answer-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                parent.querySelector('.answer-input').value = answer;
            });
        });
        
        document.getElementById('testForm').addEventListener('submit', function(e) {
            const allAnswered = document.querySelectorAll('.answer-input').length === 
                               document.querySelectorAll('.answer-input[value!=""]').length;
            
            if (!allAnswered) {
                e.preventDefault();
                alert('Пожалуйста, ответьте на все вопросы!');
            }
        });
    </script>
    <script src="../js/script.js"></script>
</body>
</html>