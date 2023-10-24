<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тестування для дитини</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f6f6f6;
        }

        .question, .result-popup {
            font-size: 24px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .result-popup {
			width: 400px;
			height: 300px;
            font-size: 36px;
            padding: 40px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            z-index: 1000;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        #questionNumber, .result-text {
            font-size: 18px;
            font-weight: bold;
        }

        #questionText {
            font-size: 32px;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"] {
            font-size: 24px;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            font-size: 18px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .stats {
            margin-top: 20px;
            font-size: 18px;
        }

        .emoji {
            font-size: 50px;
            display: inline-block;
            transition: transform 0.3s;
        }

        .emoji:hover {
            transform: scale(1.1);
        }

        /* Добавляем стили для фейерверка */
        .fireworks {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('4M57.gif') center center no-repeat;
            background-size: cover;
            z-index: 999;
            opacity: 0.8;
        }
    </style>
</head>

<body>

<div class="question">
    <span class="emoji" id="emoji">😃</span>
    <div id="questionNumber"></div>
    <div id="questionText"></div>
    <input type="text" id="answer">
    <button onclick="checkAnswer()">Перевірити</button>
    <div class="stats">
        <div>Поточний етап: <span id="currentStep"></span></div>
        <div>Правильні відповіді: <span id="correctAnswers">0</span></div>
        <div>Помилки: <span id="incorrectAnswers">0</span></div>
		<div>Оставшееся время: <span id="remainingTime">20:00</span></div>
    </div>
</div>

<div class="result-popup">
    <div class="emoji" id="resultEmoji">😃</div>
    <div class="result-text">Ваш результат: <span id="resultScore"></span></div>
    <div class="result-text" id="resultMessage"></div>
</div>

<div class="fireworks"></div>

<script>
	function initializeTest(questions) {
		let currentQuestion = 0;
		let correctCount = 0;
		let incorrectCount = 0;
		const totalQuestions = questions.length;

		let testLog = [];
		let testTimer;
		let questionTimer;
		let questionStartTime;

		let remainingTime = 20 * 60;  // 20 minutes in seconds

		// Initialize the test timer (20 minutes)
		testTimer = setTimeout(showResultPopup, 20 * 60 * 1000);

		// Timer to update the remaining time
		const remainingTimeTimer = setInterval(function() {
			remainingTime--;
			const minutes = Math.floor(remainingTime / 60);
			const seconds = remainingTime % 60;
			document.getElementById('remainingTime').innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
			if (remainingTime <= 0) {
				clearInterval(remainingTimeTimer);
			}
		}, 1000);

		// Initialize the question timer
		function startQuestionTimer() {
			questionStartTime = new Date().getTime();
			questionTimer = setInterval(function() {
				// Update the UI timer here if needed
			}, 1000);
		}

		// Stop the question timer and return the time spent on the question
		function stopQuestionTimer() {
			clearInterval(questionTimer);
			const questionEndTime = new Date().getTime();
			return (questionEndTime - questionStartTime) / 1000;
		}

		function showResultPopup() {
			clearTimeout(testTimer);
            let score = correctCount;
            let message = '';
            let emoji = '';

            // Логика отображения результатов может быть изменена
            if (score >= totalQuestions * 0.8) {
                message = 'Високий рівень';
                emoji = '😃';
                document.querySelector('.fireworks').style.display = 'block';
            } else if (score >= totalQuestions * 0.5) {
                message = 'Достатній рівень';
                emoji = '😊';
            } else {
                message = 'Початковий рівень';
                emoji = '😔';
            }

            document.getElementById('resultEmoji').innerText = emoji;
            document.getElementById('resultScore').innerText = score + '/' + totalQuestions;
            document.getElementById('resultMessage').innerText = message;
            document.querySelector('.result-popup').style.display = 'block';
           

		   // Показываем фейерверк, если рейтинг "Високий рівень"
            if (score >= totalQuestions * 0.8) {
                document.querySelector('.fireworks').style.display = 'block';
            }
			// Отправляем лог на сервер
			fetch('saveLog.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(testLog)
			}).then(response => response.json())
			  .then(data => {
				if (data.status === 'success') {
					console.log('Лог успешно сохранен');
				} else {
					console.log('Произошла ошибка при сохранении лога');
				}
			});
		}

	function checkAnswer() {
		const userAnswer = parseInt(document.getElementById('answer').value);
		const timeSpent = stopQuestionTimer();  // Stop the timer and get the time spent

		if (userAnswer === questions[currentQuestion].answer) {
			correctCount++;
			document.getElementById('correctAnswers').innerText = correctCount;
		} else {
			incorrectCount++;
			document.getElementById('incorrectAnswers').innerText = incorrectCount;
		}

		// Add to the log
		testLog.push({
			question: questions[currentQuestion],
			wasCorrect: userAnswer === questions[currentQuestion].answer,
			timeSpent
		});

		currentQuestion++;
		if (currentQuestion < totalQuestions) {
			loadNextQuestion();
		} else {
			showResultPopup();
		}
	}

        function loadNextQuestion() {
            document.getElementById('questionNumber').innerText = questions[currentQuestion].number;
            document.getElementById('questionText').innerText = questions[currentQuestion].text;
            document.getElementById('answer').value = '';
            document.getElementById('currentStep').innerText = (currentQuestion + 1) + '/' + totalQuestions;
			
			startQuestionTimer();
		}

		loadNextQuestion();
		window.checkAnswer = checkAnswer;  
	}

	const questions = [
		{ number: '1.1.', text: '9 + 6 = ?', answer: 9 + 6 },
		{ number: '1.2.', text: '15 - 7 = ?', answer: 15 - 7 },
		{ number: '1.3.', text: '12 + 3 - 4 = ?', answer: 12 + 3 - 4 },
		{ number: '1.4.', text: '(8 + 5) - 3 = ?', answer: (8 + 5) - 3 },
		{ number: '1.5.', text: '15 - 6 + 4 = ?', answer: 15 - 6 + 4 },
		{ number: '1.6.', text: '(7 + 6) - 9 = ?', answer: (7 + 6) - 9 },
		{ number: '1.7.', text: '9 + 5 - 7 = ?', answer: 9 + 5 - 7 },
		{ number: '2.1.', text: 'Якщо a = 6, обчислити 11 - a + 3', answer: 11 - 6 + 3 },
		{ number: '2.2.', text: 'Якщо a = 7, обчислити a + 10 - 4', answer: 7 + 10 - 4 },
		{ number: '2.3.', text: 'Що більше: 5 + 7 чи 13 - 7?', answer: 13 - 7 },
		{ number: '2.4.', text: 'Що менше: 10 + 5 чи 20 - 4?', answer: 10 + 5 },
		{ number: '3.1.', text: 'На озері плавають 12 лебедів. Спочатку з них вилетіло 5 лебедів, а потім ще 4. Скільки лебедів залишилось плавати?', answer: 12 - 5 - 4 },
		{ number: '3.2.', text: 'У кошику було 20 яблук. 7 яблук взяли, а потім додали 5. Скільки яблук стало в кошику?', answer: 20 - 7 + 5 },
		{ number: '3.3.', text: 'На полиці стоїть 10 книг. 3 книги взяли читати, а 2 повернули. Скільки книг залишилось на полиці?', answer: 10 - 3 + 2 },
		{ number: '3.4.', text: 'У класі 15 дітей. 5 дітей пішли на перерву, а 3 прийшли. Скільки дітей зараз у класі?', answer: 15 - 5 + 3 }
	];


    initializeTest(questions);
</script>
</body>
</html>




