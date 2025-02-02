<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://api.groq.com/openai/v1/chat/completions",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'model' => 'deepseek-r1-distill-llama-70b',
    'messages' => [
        [
                'role' => 'system',
                'content' => 'To create a clear and consistent scoring system for evaluating pinyin pronunciation, we have developed the following logic:<br><br>### Scoring Logic for Pinyin Pronunciation Evaluation<br><br>1. **Exact Match**:<br>   - If the input pinyin matches the expected pinyin exactly in both sound and tone, the score is **1**.<br><br>2. **Tone Mismatch**:<br>   - For each character where the tone does not match the expected tone, subtract **0.2** from the score.<br><br>3. **Sound Substitution**:<br>   - For each character where a similar sound is used incorrectly (based on a predefined list of similar sounds), subtract **0.2** from the score.<br><br>4. **Combination of Errors**:<br>   - If a character has both a tone mismatch and a sound substitution error, subtract **0.4** from the score for that character.<br><br>5. **Maximum Deduction**:<br>   - The score should not go below **0**.<br><br>### Predefined List of Similar Sounds<br><br>To ensure consistency, the following similar sounds will trigger the sound substitution penalty:<br><br>- `zh` and `z`<br>- `l` and `n`<br>- `in` and `ing`<br>- `en` and `eng`<br>- `min` and `ming`<br>- `tan` and `tang`<br>- `fan` and `fang`<br>- `xin` and `xing`<br>- `can` and `cang`<br><br>### Application of the Scoring Logic<br><br>- **Penalties are applied per character**. Each error in each character is penalized individually.<br>- The scoring system is designed to be transparent and easy to apply consistently across different inputs.<br><br>### Example Evaluations<br><br>1. **Example 1**:<br>   - **Expected**: `nǐ hǎo`<br>   - **Input**: `ní hào`<br>   - **Tone Mismatch**: `ní` vs. `nǐ` → subtract **0.2**<br>   - **Tone Mismatch**: `hào` vs. `hǎo` → subtract **0.2**<br>   - **Total Score**: (1 - 0.4 = 0.6)<br><br>2. **Example 2**:<br>   - **Expected**: `míng tiān`<br>   - **Input**: `mín tián`<br>   - **Sound Substitution**: `mín` for `míng` → subtract **0.2**<br>   - **Sound Substitution**: `tián` for `tiān` → subtract **0.4**<br>   - **Total Score**: (1 - 0.6 = 0.4). <Br> please only response the pinyin with final score  in JSON like {\'input\':\'ní hào\'，expect:\'nǐ hǎo\', score: 0.6}    '
        ],
        [
                'role' => 'user',
                'content' => ' expected: `明天`, input:`民天` '
        ]
    ]
  ]),
  CURLOPT_HTTPHEADER => [
    "authorization: Bearer gsk_{api_key}", // get from https://console.groq.com/keys
    "content-type: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}