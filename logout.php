<?php
session_start();
session_unset();
session_destroy();


header("Refresh: 2; url=login.php");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saindo...</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
        }

        
        .spinner {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #4c8bf5;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

       
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        
        .message {
            text-align: center;
            font-size: 1.5rem;
            margin-top: 20px;
            color: #333;
        }
    </style>
</head>
<body>





<div class="message">
    Saindo, aguarde...
</div>

</body>
</html>
