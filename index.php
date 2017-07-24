<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Archive.org Content Scraper</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css"/>
        <link rel="stylesheet" href="css/style.css"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class="container" id="container">
            <textarea id="input"></textarea>
            <div id="error" class="error"></div>
            <textarea id="output" disabled="disabled" style="height:100px">log window</textarea>
            <button id="scrape_button" class="cta">Scrape Domains!</button>
        </div>
        <script src="//code.jquery.com/jquery-latest.min.js"></script>
        <script src="js/application.js"></script>
    </body>
</html>
