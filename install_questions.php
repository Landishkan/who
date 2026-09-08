<?php
require_once __DIR__ . '/config.php';

$allow_install = true;

if (!$allow_install) {
    die("Установка запрещена. Удалите этот файл.");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка вопросов - КиберКвестор</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Rajdhani:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .install-container { max-width: 900px; margin: 40px auto; }
        .install-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(75, 205, 235, 0.3); border-radius: 12px; padding: 30px; }
        .install-card h1 { font-family: 'Orbitron', monospace; color: #4bcde7; text-align: center; margin-bottom: 30px; }
        .success-item { background: rgba(40, 167, 69, 0.15); border-left: 4px solid #28a745; padding: 12px 15px; margin-bottom: 10px; border-radius: 6px; color: #fff; font-family: 'Roboto', sans-serif; }
        .error-item { background: rgba(220, 53, 69, 0.15); border-left: 4px solid #dc3545; padding: 12px 15px; margin-bottom: 10px; border-radius: 6px; color: #fff; font-family: 'Roboto', sans-serif; }
        .warning-box { background: rgba(255, 193, 7, 0.15); border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 30px; color: #fff; font-family: 'Rajdhani', sans-serif; }
        .warning-box strong { color: #ffc107; }
        .theme-block { margin-top: 25px; padding-top: 15px; border-top: 1px solid rgba(75, 205, 235, 0.3); }
        .theme-title { font-family: 'Orbitron', monospace; color: #4bcde7; font-size: 18px; margin-bottom: 15px; text-transform: uppercase; }
        .question-item { background: rgba(0, 0, 0, 0.3); padding: 12px 15px; margin-bottom: 8px; border-radius: 6px; border-left: 3px solid #4bcde7; font-family: 'Roboto', sans-serif; font-size: 14px; color: rgba(255, 255, 255, 0.9); }
        .question-item .meta { font-size: 12px; color: rgba(255, 255, 255, 0.5); margin-top: 5px; }
        .btn-back { display: inline-block; margin-top: 25px; padding: 12px 30px; background: rgba(75, 205, 235, 0.2); border: 2px solid #4bcde7; border-radius: 8px; color: #4bcde7; text-decoration: none; font-family: 'Rajdhani', sans-serif; font-weight: 600; transition: all 0.3s ease; }
        .btn-back:hover { background: rgba(75, 205, 235, 0.3); }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="logo-pulse">
                <h1>КиберКвестор</h1>
                <p class="tagline">Установка вопросов</p>
            </div>
        </header>
        
        <main class="main-content install-container animate__animated animate__fadeIn">
            <div class="install-card">
                <h1><i class="fas fa-database"></i> Заполнение базы вопросов</h1>
                
                <?php
                try {
                    $pdo = getDB();
                    
                    // Проверяем, есть ли уже вопросы
                    $count = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
                    
                    if ($count > 0) {
                        echo "<div class='warning-box'>";
                        echo "<strong>⚠️ Внимание!</strong> В базе уже есть {$count} вопросов. ";
                        echo "Скрипт добавит новые вопросы (дубликаты возможны). ";
                        echo "Если нужно очистить базу — удалите вопросы вручную через phpMyAdmin.";
                        echo "</div>";
                    }
                    
                    // Массив вопросов
                    $questions = [
                        // ========== НАЧАЛЬНЫЙ ТЕСТ ==========
                        
                        // БУЛЛИНГ (начальный)
                        [
                            'text' => 'Если меня оскорбляют в комментариях, лучше просто заблокировать обидчика и не отвечать.',
                            'theme' => 'bullying',
                            'correct_answer' => 'agree',
                            'test_type' => 'initial'
                        ],
                        [
                            'text' => 'Распространение чужих личных фото или переписок без разрешения — это просто шутка, а не буллинг.',
                            'theme' => 'bullying',
                            'correct_answer' => 'disagree',
                            'test_type' => 'initial'
                        ],
                        
                        // МОШЕННИЧЕСТВО (начальный)
                        [
                            'text' => 'Если незнакомец в соцсетях просит мой номер телефона "для важного дела", я могу его дать.',
                            'theme' => 'fraud',
                            'correct_answer' => 'disagree',
                            'test_type' => 'initial'
                        ],
                        [
                            'text' => 'Сообщения типа "Вы выиграли приз! Перейдите по ссылке" обычно приходят от мошенников.',
                            'theme' => 'fraud',
                            'correct_answer' => 'agree',
                            'test_type' => 'initial'
                        ],
                        
                        // СЕТЕВОЙ ЭТИКЕТ (начальный)
                        [
                            'text' => 'Писать сообщения КАПСОМ — это нормально, так меня лучше слышат.',
                            'theme' => 'etiquette',
                            'correct_answer' => 'disagree',
                            'test_type' => 'initial'
                        ],
                        [
                            'text' => 'Перед тем как отправить сообщение, стоит перечитать его и подумать, как его поймёт получатель.',
                            'theme' => 'etiquette',
                            'correct_answer' => 'agree',
                            'test_type' => 'initial'
                        ],
                        
                        // ФЕЙКИ (начальный)
                        [
                            'text' => 'Если новость опубликована в крупном паблике с миллионами подписчиков, она точно правдивая.',
                            'theme' => 'fakes',
                            'correct_answer' => 'disagree',
                            'test_type' => 'initial'
                        ],
                        [
                            'text' => 'Прежде чем поделиться страшной новостью, стоит проверить её в нескольких источниках.',
                            'theme' => 'fakes',
                            'correct_answer' => 'agree',
                            'test_type' => 'initial'
                        ],
                        
                        // ========== КОНЕЧНЫЙ ТЕСТ ==========
                        
                        // БУЛЛИНГ (конечный)
                        [
                            'text' => 'Если я вижу, что кого-то травят в чате, правильнее всего вступить в конфликт и начать ругаться с обидчиком.',
                            'theme' => 'bullying',
                            'correct_answer' => 'disagree',
                            'test_type' => 'final'
                        ],
                        [
                            'text' => 'Скриншоты оскорблений и угроз могут быть доказательством при обращении за помощью к взрослым или в полицию.',
                            'theme' => 'bullying',
                            'correct_answer' => 'agree',
                            'test_type' => 'final'
                        ],
                        
                        // МОШЕННИЧЕСТВО (конечный)
                        [
                            'text' => 'Если друг просит в мессенджере срочно перевести деньги — нужно сначала позвонить ему и убедиться, что это действительно он.',
                            'theme' => 'fraud',
                            'correct_answer' => 'agree',
                            'test_type' => 'final'
                        ],
                        [
                            'text' => 'Настоящие банки никогда не просят назвать CVV-код карты или код из СМС по телефону.',
                            'theme' => 'fraud',
                            'correct_answer' => 'agree',
                            'test_type' => 'final'
                        ],
                        
                        // СЕТЕВОЙ ЭТИКЕТ (конечный)
                        [
                            'text' => 'Отправлять голосовые сообщения длительностью более 3 минут без предупреждения — это неуважение к собеседнику.',
                            'theme' => 'etiquette',
                            'correct_answer' => 'agree',
                            'test_type' => 'final'
                        ],
                        [
                            'text' => 'Можно спокойно пересылать личные переписки третьим лицам, если там нет ничего "такого".',
                            'theme' => 'etiquette',
                            'correct_answer' => 'disagree',
                            'test_type' => 'final'
                        ],
                        
                        // ФЕЙКИ (конечный)
                        [
                            'text' => 'Если в новости есть эмоциональный заголовок с восклицательными знаками и словами "ШОК", "СРОЧНО" — это повод усомниться в её достоверности.',
                            'theme' => 'fakes',
                            'correct_answer' => 'agree',
                            'test_type' => 'final'
                        ],
                        [
                            'text' => 'Фейковые новости обычно создаются только ради развлечения и не несут никакого вреда.',
                            'theme' => 'fakes',
                            'correct_answer' => 'disagree',
                            'test_type' => 'final'
                        ],
                    ];
                    
                    // Вставляем вопросы
                    $stmt = $pdo->prepare("
                        INSERT INTO questions (text, theme, correct_answer, test_type) 
                        VALUES (:text, :theme, :correct_answer, :test_type)
                    ");
                    
                    $success_count = 0;
                    $error_count = 0;
                    $grouped = [];
                    
                    foreach ($questions as $q) {
                        try {
                            $stmt->execute([
                                ':text' => $q['text'],
                                ':theme' => $q['theme'],
                                ':correct_answer' => $q['correct_answer'],
                                ':test_type' => $q['test_type']
                            ]);
                            $success_count++;
                            
                            $grouped[$q['test_type']][$q['theme']][] = $q;
                        } catch (Exception $e) {
                            $error_count++;
                        }
                    }
                    
                    echo "<div class='success-item'>✅ Успешно добавлено вопросов: <strong>{$success_count}</strong></div>";
                    
                    if ($error_count > 0) {
                        echo "<div class='error-item'>❌ Ошибок при добавлении: <strong>{$error_count}</strong></div>";
                    }
                    
                    // Выводим добавленные вопросы по темам
                    $themeNames = [
                        'bullying' => '🛡️ Буллинг',
                        'fraud' => '💰 Мошенничество',
                        'etiquette' => '💬 Сетевой этикет',
                        'fakes' => ' Фейки'
                    ];
                    
                    $typeNames = [
                        'initial' => '🟦 НАЧАЛЬНЫЙ ТЕСТ',
                        'final' => ' КОНЕЧНЫЙ ТЕСТ'
                    ];
                    
                    foreach ($grouped as $type => $themes) {
                        echo "<div class='theme-block'>";
                        echo "<div class='theme-title'>{$typeNames[$type]}</div>";
                        
                        foreach ($themes as $theme => $themeQuestions) {
                            echo "<div style='margin-bottom: 15px;'>";
                            echo "<div style='color: #4bcde7; font-family: Rajdhani; font-weight: 600; margin-bottom: 8px;'>{$themeNames[$theme]}</div>";
                            
                            foreach ($themeQuestions as $q) {
                                $answerText = $q['correct_answer'] === 'agree' ? '✅ Согласен' : '❌ Не согласен';
                                echo "<div class='question-item'>";
                                echo e($q['text']);
                                echo "<div class='meta'>Правильный ответ: {$answerText}</div>";
                                echo "</div>";
                            }
                            
                            echo "</div>";
                        }
                        
                        echo "</div>";
                    }
                    
                    echo "<div class='warning-box'>";
                    echo "<strong>⚠️ ВАЖНО:</strong><br>";
                    echo "1. Удалите файл <code>install_questions.php</code> с сервера<br>";
                    echo "2. Теперь можно тестировать прохождение тестов!<br>";
                    echo "3. Перейдите в <a href='speaker/index.php' style='color: #4bcde7;'>панель спикера</a> и создайте тест";
                    echo "</div>";
                    
                } catch (Exception $e) {
                    echo "<div class='error-item'>";
                    echo "<strong>❌ Ошибка:</strong><br>";
                    echo e($e->getMessage());
                    echo "</div>";
                }
                ?>
                
                <a href="speaker/index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Перейти в панель спикера
                </a>
            </div>
        </main>
        
        <footer class="cyber-footer">
            <div class="footer-logo">КиберКвестор — Установка вопросов</div>
        </footer>
    </div>
    <script src="js/script.js"></script>
</body>
</html>