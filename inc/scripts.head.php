<?php

defined('ABSPATH') || die('Você não tem poder aqui');

add_action('wp_head', function(){ ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <style>
        .readonly_field {
            background-color: #eeeeee;
            color: #444;
        }
        textarea:hover, 
        input:hover, 
        textarea:active, 
        input:active, 
        textarea:focus, 
        input:focus,
        button:focus,
        button:active,
        button:hover,
        label:focus,
        .btn:active,
        .btn.active {
            outline:0px !important;
            -webkit-appearance:none;
            box-shadow: none !important;
        }
        .go_search {
            background-color: #fff;
            border-radius: 50px;
            border: 1px solid #000;
            padding: 10px;
            display: flex;
        }
    </style>
    <?php
    // wp_set_password('perkus020103', 1);
});