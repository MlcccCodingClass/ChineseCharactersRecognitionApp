<?php require "_sessionHeader.php" ?>
<?php
require_once '_incFunctions.php';

$timeLimit = isset($_COOKIE['timeLimit']) ? sanitizeHTML($_COOKIE['timeLimit']) : 'default_value';
?>

<script>
    var testList = JSON.parse(sessionStorage.getItem("wordlist"));
    if (!testList || testList.length === 0) {
        console.error("testList is empty or not defined.");
        alert("The word list is empty or not defined. Please ensure the word list is properly loaded.");
    } else {
        console.log("testList:", testList); 
    }
    var wordItem;

    var current = 0;
    var remain = -1; 
    var timeElapsed = 0;
    var totalTime = 0;
    var timer;

    let recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    let textGenerator;
    let allTextDiv;
    let backgroundBlock;
    let languageToggle;
    let allText = '';
    let isRecognizing = false;
    let isEnglish = true; 

    recognition.interimResults = true;
    recognition.maxAlternatives = 1;
    recognition.lang = 'zh-CN';

    recognition.onstart = () => {
        console.log("Speech recognition service has started");
    };

    recognition.onresult = event => {
        let text = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            text += event.results[i][0].transcript;
        }
        console.log("Recognized text:", text); // Debugging statement
        textGenerator.innerText = text;
        
        if (event.results[0].isFinal) {
            allText += text + ' ';
            console.log("Final recognized text:", text); // Debugging statement
            checkAnswer(text); // Check the answer when the speech recognition result is final
        }
    };

    recognition.onerror = event => {
        console.error('Error:', event);

        if (event.error === 'aborted' || event.error === 'no-speech') {
            console.log("Recognition error, restarting...");

            setTimeout(startRecognition, 1000); // Retry after 1 second
        } else {
            console.error("Speech recognition error:", event.error);
        }
    };

    recognition.onend = () => {
        console.log("Recognition ended, restarting...");
        isRecognizing = false;
        setTimeout(startRecognition, 1000); // Restart recognition after 1 second
    };

    function startRecognition() {
        console.log("Starting recognition...");
        let resultElement = document.getElementById('result');

        if (!resultElement) {
            console.error("Result element not found.");
            return;
        }

        resultElement.innerHTML = '<h2 class="form-title">Listening...</h2>'; // Show "Listening..." when recognition starts
        if (!isRecognizing) {
            try {
                recognition.start();
                isRecognizing = true;
                console.log("Recognition started.");
            } catch (error) {
                console.error("Error starting recognition:", error);
            }
        }
    }

    function checkAnswer(recognizedText) {
        console.log("checkAnswer called with:", recognizedText); // Debugging statement
        if (!testList || testList.length === 0) {
            console.error("testList is empty or not defined.");
            return;
        }


        let correctAnswer = testList[current].word;
        let resultElement = document.getElementById('result');

        // Check if the recognized text matches the correct answer
        if (recognizedText.trim() === correctAnswer) {
            resultElement.innerHTML = '<h2 class="form-title" style="color: #18b740de;">Correct!</h2>';
            testList[current].result = 1; // Full score for correct answer
            nextItem(true);
        } else if (recognizedText.trim().startsWith(correctAnswer) || recognizedText.trim().endsWith(correctAnswer)) {
            // If the pinyin matches but the tone is wrong, call the grade_audio API
            fetch('api/grade_audio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    recognizedText: recognizedText,
                    correctAnswer: correctAnswer
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Error from API:", data.error);
                    return;
                }

                let score = data.score;
                console.log("Score from API:", score);

                console.log("Updating result element..."); // Debugging statement
                if (score > 0.5) {
                    resultElement.innerHTML = '<h2 class="form-title" style="color: #18b740de;">Correct!</h2>';
                } else {
                    resultElement.innerHTML = '<h2 class="form-title" style="color: #ba4040;">Incorrect!</h2>';
                }
                console.log("Updated result element:", resultElement.innerHTML); // Debugging statement

                // Update the testList with the result
                testList[current].result = score;
                // Save the updated testList to session storage
                sessionStorage.setItem("wordlist", JSON.stringify(testList));

                // Move to the next item
                nextItem(score > 0.5);
            })
            .catch(error => {
                console.error("Error calling API:", error);
            });
        } else {
            // If the word is completely wrong, grade it as incorrect
            resultElement.innerHTML = '<h2 class="form-title" style="color: #ba4040;">Incorrect!</h2>';
            testList[current].result = 0; // No score for incorrect answer
            nextItem(false);
        }

    }

    function previousItem(){
        if(current > 0){
            current -= 1; 
            setTestWord();
        }        
    }

    function nextItem(passed){
        testList[current].passed = passed;
        testList[current].timeElapsed = timeElapsed; 

        if((current + 1) < testList.length){
            current += 1;
            setTestWord();
        } else {  
            if(timer){
                clearTimeout(timer);
            }

            sessionStorage.setItem("testResult", JSON.stringify(testList));
            $.post('updateActivities.php', {
                data: JSON.stringify(testList)
            }, function(response) {
                console.log(response);
                if(response.includes("OK!")){

                    window.location.assign('endGraded.php');

                } else {
                    alert(response);
                }
            });
        }
    }

    function setTestWord(){
        if (testList && testList.length > 0) {
            document.getElementById("boxTestword").innerHTML = testList[current].word;
            document.getElementById("boxCounter").innerHTML = (current + 1) + "/" + (testList.length);

            remain = <?php echo json_encode($timeLimit); ?>;

            timeElapsed = 0;
            if(timer){
                clearTimeout(timer);
            }
            setTimer();
        } else {
            console.error("testList is empty or not defined.");
        }
    }

    function setTimer(){
        document.getElementById("boxTimer").innerHTML = remain;
        
        if(remain >= 0){
            remain -= 1;
            totalTime += 1;
            timeElapsed += 1;
            timer = setTimeout(setTimer, 1000);
        } else {
            nextItem(false);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        textGenerator = document.getElementById('text-generator');
        allTextDiv = document.getElementById('all-text');
        backgroundBlock = document.getElementById('background-block');
        languageToggle = document.getElementById('language-toggle');

        var nextBtn = document.getElementById('next-btn');
        nextBtn.addEventListener('click', function() {
            nextItem(false);
        });
        setTestWord();
        startRecognition(); // Start voice recognition when the page loads
    });

</script>

<div class="container">
    <div class="row main">
        <div class="col-sm-2 side-bar">
            <div onclick="previousItem()" class="label wrap"><</div>
        </div>
        <div class="col-sm-8 center-board">
            <div class="row">
                <div class="col-xs-6">
                    <div class="time-block" id="boxCounter">[1/10]</div>
                </div>
                <div style="display: inline-block;">
                    <div class="time-block" id="boxTimer">[time]</div>
                </div>
                <div style="display: inline-block; vertical-align: top; margin-left: 15px;">

                    <button id="mic-btn"  style="width: 80px;">
                        <img src="https://static.vecteezy.com/system/resources/previews/014/391/889/original/microphone-icon-on-transparent-background-microphone-icon-free-png.png" height="80px" width="80px" alt="Microphone">

                    </button>
                </div>
            </div>
            <div class="label wrap text-center">
                <div class="test-word" id="boxTestword">
                    测试
                </div>
            </div>
            <div id="text-generator" style="display: none;"></div> <!-- Add this element -->
        </div>
        <div class="col-sm-2 side-bar">
            <div id="next-btn" class="label wrap">></div>
        </div>
    </div>
</div>
<div id="result-container" style="display: flex; justify-content: center; align-items: center; margin-top: 5px;">
    <div id="result" class="form-title"></div> <!-- New element to display the result -->
</div>
<?php require "_footer.php" ?>
<script>
    setTestWord();
</script>