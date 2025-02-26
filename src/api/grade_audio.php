<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['recognizedText']) || !isset($data['correctAnswer'])) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $recognizedText = $data['recognizedText'];
    $correctAnswer = $data['correctAnswer'];

    $score = gradeAnswer($recognizedText, $correctAnswer);
    echo json_encode(['score' => $score]);
}

function gradeAnswer($recognizedText, $correctAnswer) {
    $similarSounds = [
        'zh' => 'z', 'z' => 'zh',
        'l' => 'n', 'n' => 'l',
        'in' => 'ing', 'ing' => 'in',
        'en' => 'eng', 'eng' => 'en',
        'min' => 'ming', 'ming' => 'min',
        'tan' => 'tang', 'tang' => 'tan',
        'fan' => 'fang', 'fang' => 'fan',
        'xin' => 'xing', 'xing' => 'xin',
        'can' => 'cang', 'cang' => 'can'
    ];

    $score = 1.0;
    $recognizedParts = explode(' ', $recognizedText);
    $correctParts = explode(' ', $correctAnswer);

    foreach ($correctParts as $index => $correctPart) {
        $recognizedPart = $recognizedParts[$index] ?? '';

        if ($correctPart === $recognizedPart) {
            continue;
        }

        $correctTone = substr($correctPart, -1);
        $recognizedTone = substr($recognizedPart, -1);

        if ($correctTone !== $recognizedTone) {
            $score -= 0.2;
        }

        $correctSound = substr($correctPart, 0, -1);
        $recognizedSound = substr($recognizedPart, 0, -1);

        if ($correctSound !== $recognizedSound) {
            if (isset($similarSounds[$correctSound]) && $similarSounds[$correctSound] === $recognizedSound) {
                $score -= 0.2;
            } else {
                $score -= 0.5;
            }
        }

        if ($correctTone !== $recognizedTone && $correctSound !== $recognizedSound) {
            $score -= 0.2; // Additional penalty for combination of errors
        }
    }

    return max($score, 0); // Ensure the score does not go below 0
}
?>