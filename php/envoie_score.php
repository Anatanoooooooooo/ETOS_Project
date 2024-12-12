<?php
header('Content-Type: application/json');

// Chemin vers le fichier JSON (assurez-vous que ce fichier est mis à jour par Unity)
$jsonFile = 'score.json';

// Lire le contenu du fichier JSON
$existingData = [];
if (file_exists($jsonFile)) {
    $existingData = json_decode(file_get_contents($jsonFile), true);
}

// Connexion à la base de données MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scoreboard";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fonction pour insérer un score dans la base de données
function insertScore($pseudo, $score, $conn) {
    $stmt = $conn->prepare("INSERT INTO score (pseudo, score) VALUES (?, ?)");
    $stmt->bind_param("si", $pseudo, $score);
    $stmt->execute();
    $stmt->close();
}

// Fonction pour vérifier si de nouvelles données sont présentes dans le JSON
function checkNewScores($existingData, $jsonFile, $conn) {
    $newData = json_decode(file_get_contents($jsonFile), true);

    if (!empty($newData)) {
        foreach ($newData as $scoreEntry) {
            // Vérifier si la donnée est déjà présente (vous pouvez ajouter des validations supplémentaires ici)
            if (!in_array($scoreEntry, $existingData)) {
                // Si de nouvelles données sont trouvées, les insérer dans la base de données
                insertScore($scoreEntry['pseudo'], $scoreEntry['score'], $conn);
            }
        }

        // Mettre à jour les données existantes après insertion
        file_put_contents($jsonFile, json_encode($newData));
    }
}

// Vérifier toutes les 10 secondes
while (true) {
    checkNewScores($existingData, $jsonFile, $conn);

    // Attendre 10 secondes avant de vérifier à nouveau
    sleep(10);
}

$conn->close();
?>
