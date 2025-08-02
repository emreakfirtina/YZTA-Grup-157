<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_message = $_POST["message"];

    $api_key = "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";

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "Sen sürdürülebilir gıda sistemleri hakkında sade ve yardımcı bir chatbot'sun."],
            ["role" => "user", "content" => $user_message]
        ]
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result["error"])) {
        echo "Hata: " . $result["error"]["message"];
        exit;
    }

    if ($http_code !== 200) {
        echo "API Hatası (HTTP $http_code)";
        exit;
    }

    $reply = $result["choices"][0]["message"]["content"] ?? "Bot cevap veremedi.";
    echo $reply;
}
?>


