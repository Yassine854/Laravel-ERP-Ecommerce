<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires du client</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #f9f9f9;
            border: 1px solid #dddddd;
        }
        .section p {
            margin: 0;
            color: #555555;
            line-height: 1.6;
        }
        .footer {
            text-align: center;
            font-size: 0.9em;
            color: #777777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirmation de Soumission de Formulaire</h1>
        <div class="section">
            <p><strong>Sujet :</strong> {{ $contact['name'] }}</p>
        </div>
        <div class="section">
            <p><strong>Email :</strong> {{ $contact['mail'] }}</p>
        </div>
        <div class="section">
            <p><strong>Téléphone :</strong> {{ $contact['mobile'] }}</p>
        </div>
        <div class="section">
            <p><strong>Message :</strong></p>
            <p>{{ $contact['message'] }}</p>
        </div>
        <div class="footer">
            <p>Merci de nous avoir contacté. Nous vous répondrons dans les plus brefs délais.</p>
        </div>
    </div>
</body>
</html>
